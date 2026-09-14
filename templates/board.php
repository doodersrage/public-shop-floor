<?php
/**
 * Public shop-floor board markup.
 *
 * Expects local scope vars from PSFloor_Frontend::render_board():
 * $stations, $show_product, $jobs, $shop.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p class="psf-kicker"><?php echo esc_html__( 'Live floor', 'public-shop-floor' ); ?></p>
<h1><?php echo esc_html__( 'Shop floor', 'public-shop-floor' ); ?></h1>
<p class="psf-lede"><?php echo esc_html( PSFloor_Plugin::setting( 'intro' ) ); ?></p>

<?php if ( ! $jobs ) : ?>
	<div class="psf-empty">
		<h2><?php echo esc_html__( 'The floor is idle', 'public-shop-floor' ); ?></h2>
		<p><?php echo esc_html__( 'No made-to-order jobs are in the shop right now. When an order is paid, it will show up at the first station.', 'public-shop-floor' ); ?></p>
	</div>
<?php else : ?>
	<p class="psf-count">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: number of jobs currently on the floor */
				_n( '%s job on the floor', '%s jobs on the floor', count( $jobs ), 'public-shop-floor' ),
				number_format_i18n( count( $jobs ) )
			)
		);
		?>
	</p>
	<div class="psf-board" role="list">
		<?php foreach ( $stations as $psf_station ) : ?>
			<section class="psf-station" role="listitem" aria-label="<?php echo esc_attr( $psf_station['label'] ); ?>">
				<h2><?php echo esc_html( $psf_station['label'] ); ?></h2>
				<?php
				$psf_column = PSFloor_Jobs::at_station( $psf_station['key'] );
				if ( ! $psf_column ) :
					?>
					<p class="psf-idle"><?php echo esc_html__( 'Idle', 'public-shop-floor' ); ?></p>
				<?php else : ?>
					<ol class="psf-queue">
						<?php foreach ( $psf_column as $psf_i => $psf_job ) : ?>
							<li class="<?php echo 'held' === $psf_job['status'] ? 'is-held' : ''; ?>">
								<a href="<?php echo esc_url( home_url( '/shop-floor/job/' . $psf_job['token'] . '/' ) ); ?>">
									<span class="psf-no"><?php echo esc_html( $psf_job['job_number'] ); ?></span>
									<?php if ( $show_product ) : ?>
										<span class="psf-prod"><?php echo esc_html( PSFloor_Jobs::product_title( $psf_job ) ); ?></span>
									<?php endif; ?>
									<span class="psf-age"><?php echo 'held' === $psf_job['status'] ? esc_html__( 'Held', 'public-shop-floor' ) : esc_html( PSFloor_Jobs::age_label( $psf_job ) ); ?></span>
									<?php if ( 'held' !== $psf_job['status'] ) : ?>
										<span class="psf-place">
											<?php
											if ( 0 === $psf_i ) {
												echo esc_html__( 'At the bench', 'public-shop-floor' );
											} else {
												echo esc_html(
													sprintf(
														/* translators: %d: number of jobs ahead in line */
														_n( '%d ahead', '%d ahead', $psf_i, 'public-shop-floor' ),
														$psf_i
													)
												);
											}
											?>
										</span>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
