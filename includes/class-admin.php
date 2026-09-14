<?php
/**
 * Merchant floor and settings.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSFloor_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_psf_move', array( __CLASS__, 'handle_move' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Shop floor', 'public-shop-floor' ),
			__( 'Shop floor', 'public-shop-floor' ),
			'manage_woocommerce',
			'psf-floor',
			array( __CLASS__, 'render_floor' )
		);
		add_submenu_page(
			'woocommerce',
			__( 'Shop floor settings', 'public-shop-floor' ),
			__( 'Floor settings', 'public-shop-floor' ),
			'manage_woocommerce',
			'psf-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	public static function assets( $hook ) {
		if ( ! in_array( $hook, array( 'woocommerce_page_psf-floor', 'woocommerce_page_psf-settings' ), true ) ) {
			return;
		}
		wp_enqueue_style( 'psf-admin', PSFLOOR_URL . 'assets/css/admin.css', array(), PSFLOOR_VERSION );
	}

	public static function register_settings() {
		register_setting(
			'psf_settings_group',
			PSFloor_Plugin::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);
	}

	public static function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		if ( isset( $input['stations_text'] ) ) {
			PSFloor_Stations::save_from_text( $input['stations_text'] );
		}
		return array(
			'shop_name'    => sanitize_text_field( $input['shop_name'] ?? '' ),
			'intro'        => sanitize_textarea_field( $input['intro'] ?? '' ),
			'public_board' => empty( $input['public_board'] ) ? '0' : '1',
			'show_product' => empty( $input['show_product'] ) ? '0' : '1',
		);
	}

	public static function handle_move() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'public-shop-floor' ) );
		}
		check_admin_referer( 'psf_move' );
		$id     = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
		$action = isset( $_POST['psf_action'] ) ? sanitize_key( wp_unslash( $_POST['psf_action'] ) ) : '';
		switch ( $action ) {
			case 'advance':
				PSFloor_Jobs::advance( $id );
				break;
			case 'back':
				PSFloor_Jobs::send_back( $id );
				break;
			case 'hold':
				PSFloor_Jobs::hold( $id );
				break;
			case 'resume':
				PSFloor_Jobs::resume( $id );
				break;
			case 'complete':
				PSFloor_Jobs::complete( $id );
				break;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=psf-floor' ) );
		exit;
	}

	public static function render_floor() {
		$stations = PSFloor_Stations::all();
		?>
		<div class="wrap psf-admin-floor">
			<h1><?php echo esc_html__( 'Shop floor', 'public-shop-floor' ); ?></h1>
			<p><?php echo esc_html__( 'Move a job when the station actually finished the work. The public board follows this, not a courier API.', 'public-shop-floor' ); ?></p>
			<?php if ( ! PSFloor_Jobs::on_floor() ) : ?>
				<div class="psf-empty">
					<p><?php echo esc_html__( 'Nothing is on the floor. Paid made-to-order orders will show up here.', 'public-shop-floor' ); ?></p>
				</div>
			<?php endif; ?>
			<div class="psf-kanban">
				<?php foreach ( $stations as $station ) : ?>
					<section class="psf-col">
						<h2><?php echo esc_html( $station['label'] ); ?></h2>
						<?php
						$jobs = PSFloor_Jobs::at_station( $station['key'] );
						if ( ! $jobs ) :
							?>
							<p class="psf-col-empty"><?php echo esc_html__( 'Idle', 'public-shop-floor' ); ?></p>
						<?php else : ?>
							<?php foreach ( $jobs as $job ) : ?>
								<?php self::card( $job, $station ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public static function card( $job, $station ) {
		$held = 'held' === $job['status'];
		?>
		<article class="psf-card<?php echo $held ? ' is-held' : ''; ?>">
			<p class="psf-job-no"><?php echo esc_html( $job['job_number'] ); ?></p>
			<p class="psf-job-product"><?php echo esc_html( PSFloor_Jobs::product_title( $job ) ); ?></p>
			<p class="psf-job-meta">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: order ID, 2: time at station */
						__( 'Order #%1$s · %2$s', 'public-shop-floor' ),
						(string) $job['order_id'],
						PSFloor_Jobs::age_label( $job )
					)
				);
				?>
			</p>
			<?php if ( $held ) : ?>
				<p class="psf-held-flag"><?php echo esc_html__( 'Held', 'public-shop-floor' ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="psf-moves">
				<?php wp_nonce_field( 'psf_move' ); ?>
				<input type="hidden" name="action" value="psf_move" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( (string) $job['id'] ); ?>" />
				<?php if ( $held ) : ?>
					<button type="submit" name="psf_action" value="resume"><?php echo esc_html__( 'Resume', 'public-shop-floor' ); ?></button>
				<?php else : ?>
					<?php if ( PSFloor_Stations::prev_key( $job['station_key'] ) ) : ?>
						<button type="submit" name="psf_action" value="back"><?php echo esc_html__( 'Send back', 'public-shop-floor' ); ?></button>
					<?php endif; ?>
					<button type="submit" name="psf_action" value="hold"><?php echo esc_html__( 'Hold', 'public-shop-floor' ); ?></button>
					<?php if ( PSFloor_Stations::next_key( $job['station_key'] ) ) : ?>
						<button type="submit" name="psf_action" value="advance" class="primary"><?php echo esc_html__( 'Advance to next', 'public-shop-floor' ); ?></button>
					<?php else : ?>
						<button type="submit" name="psf_action" value="complete" class="primary"><?php echo esc_html__( 'Leave the floor', 'public-shop-floor' ); ?></button>
					<?php endif; ?>
				<?php endif; ?>
			</form>
		</article>
		<?php
		unset( $station );
	}

	public static function render_settings() {
		$s = PSFloor_Plugin::settings();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Floor settings', 'public-shop-floor' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'psf_settings_group' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="psf_shop_name"><?php echo esc_html__( 'Shop name on the public board', 'public-shop-floor' ); ?></label></th>
						<td><input type="text" class="regular-text" id="psf_shop_name" name="<?php echo esc_attr( PSFloor_Plugin::OPTION ); ?>[shop_name]" value="<?php echo esc_attr( $s['shop_name'] ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="psf_intro"><?php echo esc_html__( 'Board introduction', 'public-shop-floor' ); ?></label></th>
						<td><textarea class="large-text" rows="4" id="psf_intro" name="<?php echo esc_attr( PSFloor_Plugin::OPTION ); ?>[intro]"><?php echo esc_textarea( $s['intro'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php echo esc_html__( 'Public board', 'public-shop-floor' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( PSFloor_Plugin::OPTION ); ?>[public_board]" value="1" <?php checked( $s['public_board'], '1' ); ?> /> <?php echo esc_html__( 'Anyone can see jobs on /shop-floor/ (no customer names)', 'public-shop-floor' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php echo esc_html__( 'Product names', 'public-shop-floor' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( PSFloor_Plugin::OPTION ); ?>[show_product]" value="1" <?php checked( $s['show_product'], '1' ); ?> /> <?php echo esc_html__( 'Show product titles on the public board', 'public-shop-floor' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><label for="psf_stations"><?php echo esc_html__( 'Stations', 'public-shop-floor' ); ?></label></th>
						<td>
							<p class="description"><?php echo wp_kses_post( __( 'One station per line: <code>Label | key | verb</code>', 'public-shop-floor' ) ); ?></p>
							<textarea class="large-text code" rows="8" id="psf_stations" name="<?php echo esc_attr( PSFloor_Plugin::OPTION ); ?>[stations_text]"><?php echo esc_textarea( PSFloor_Stations::to_text() ); ?></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save floor settings', 'public-shop-floor' ) ); ?>
			</form>
		</div>
		<?php
	}
}
