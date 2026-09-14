<?php
/**
 * Merchant floor and settings.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSF_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_psf_move', array( __CLASS__, 'handle_move' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_submenu_page(
			'woocommerce',
			'Shop floor',
			'Shop floor',
			'manage_woocommerce',
			'psf-floor',
			array( __CLASS__, 'render_floor' )
		);
		add_submenu_page(
			'woocommerce',
			'Shop floor settings',
			'Floor settings',
			'manage_woocommerce',
			'psf-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	public static function assets( $hook ) {
		if ( ! in_array( $hook, array( 'woocommerce_page_psf-floor', 'woocommerce_page_psf-settings' ), true ) ) {
			return;
		}
		wp_enqueue_style( 'psf-admin', PSF_URL . 'assets/css/admin.css', array(), PSF_VERSION );
	}

	public static function register_settings() {
		register_setting(
			'psf_settings_group',
			PSF_Plugin::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);
	}

	public static function sanitize_settings( $input ) {
		$input = is_array( $input ) ? $input : array();
		if ( isset( $input['stations_text'] ) ) {
			PSF_Stations::save_from_text( $input['stations_text'] );
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
			wp_die( 'Not allowed.' );
		}
		check_admin_referer( 'psf_move' );
		$id     = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
		$action = isset( $_POST['psf_action'] ) ? sanitize_key( wp_unslash( $_POST['psf_action'] ) ) : '';
		switch ( $action ) {
			case 'advance':
				PSF_Jobs::advance( $id );
				break;
			case 'back':
				PSF_Jobs::send_back( $id );
				break;
			case 'hold':
				PSF_Jobs::hold( $id );
				break;
			case 'resume':
				PSF_Jobs::resume( $id );
				break;
			case 'complete':
				PSF_Jobs::complete( $id );
				break;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=psf-floor' ) );
		exit;
	}

	public static function render_floor() {
		$stations = PSF_Stations::all();
		?>
		<div class="wrap psf-admin-floor">
			<h1>Shop floor</h1>
			<p>Move a job when the station actually finished the work. The public board follows this, not a courier API.</p>
			<?php if ( ! PSF_Jobs::on_floor() ) : ?>
				<div class="psf-empty">
					<p>Nothing is on the floor. Paid made-to-order orders will show up here.</p>
				</div>
			<?php endif; ?>
			<div class="psf-kanban">
				<?php foreach ( $stations as $station ) : ?>
					<section class="psf-col">
						<h2><?php echo esc_html( $station['label'] ); ?></h2>
						<?php
						$jobs = PSF_Jobs::at_station( $station['key'] );
						if ( ! $jobs ) :
							?>
							<p class="psf-col-empty">Idle</p>
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
			<p class="psf-job-product"><?php echo esc_html( PSF_Jobs::product_title( $job ) ); ?></p>
			<p class="psf-job-meta">Order #<?php echo esc_html( (string) $job['order_id'] ); ?> · <?php echo esc_html( PSF_Jobs::age_label( $job ) ); ?></p>
			<?php if ( $held ) : ?>
				<p class="psf-held-flag">Held</p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="psf-moves">
				<?php wp_nonce_field( 'psf_move' ); ?>
				<input type="hidden" name="action" value="psf_move" />
				<input type="hidden" name="job_id" value="<?php echo esc_attr( (string) $job['id'] ); ?>" />
				<?php if ( $held ) : ?>
					<button name="psf_action" value="resume">Resume</button>
				<?php else : ?>
					<?php if ( PSF_Stations::prev_key( $job['station_key'] ) ) : ?>
						<button name="psf_action" value="back">Send back</button>
					<?php endif; ?>
					<button name="psf_action" value="hold">Hold</button>
					<?php if ( PSF_Stations::next_key( $job['station_key'] ) ) : ?>
						<button name="psf_action" value="advance" class="primary">Advance to next</button>
					<?php else : ?>
						<button name="psf_action" value="complete" class="primary">Leave the floor</button>
					<?php endif; ?>
				<?php endif; ?>
			</form>
		</article>
		<?php
		unset( $station );
	}

	public static function render_settings() {
		$s = PSF_Plugin::settings();
		?>
		<div class="wrap">
			<h1>Floor settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'psf_settings_group' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="psf_shop_name">Shop name on the public board</label></th>
						<td><input type="text" class="regular-text" id="psf_shop_name" name="<?php echo esc_attr( PSF_Plugin::OPTION ); ?>[shop_name]" value="<?php echo esc_attr( $s['shop_name'] ); ?>" /></td>
					</tr>
					<tr>
						<th><label for="psf_intro">Board introduction</label></th>
						<td><textarea class="large-text" rows="4" id="psf_intro" name="<?php echo esc_attr( PSF_Plugin::OPTION ); ?>[intro]"><?php echo esc_textarea( $s['intro'] ); ?></textarea></td>
					</tr>
					<tr>
						<th>Public board</th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( PSF_Plugin::OPTION ); ?>[public_board]" value="1" <?php checked( $s['public_board'], '1' ); ?> /> Anyone can see jobs on /shop-floor/ (no customer names)</label>
						</td>
					</tr>
					<tr>
						<th>Product names</th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( PSF_Plugin::OPTION ); ?>[show_product]" value="1" <?php checked( $s['show_product'], '1' ); ?> /> Show product titles on the public board</label>
						</td>
					</tr>
					<tr>
						<th><label for="psf_stations">Stations</label></th>
						<td>
							<p class="description">One station per line: <code>Label | key | verb</code></p>
							<textarea class="large-text code" rows="8" id="psf_stations" name="<?php echo esc_attr( PSF_Plugin::OPTION ); ?>[stations_text]"><?php echo esc_textarea( PSF_Stations::to_text() ); ?></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Save floor settings' ); ?>
			</form>
		</div>
		<?php
	}
}
