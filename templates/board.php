<?php
/**
 * Public shop-floor board.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( '1' !== PSF_Plugin::setting( 'public_board', '1' ) ) {
	wp_die( 'The shop floor is not public.', 'Shop floor', array( 'response' => 403 ) );
}

$stations     = PSF_Stations::all();
$show_product = '1' === PSF_Plugin::setting( 'show_product', '1' );
$jobs         = PSF_Jobs::on_floor();

require PSF_DIR . 'templates/header.php';
?>

<p class="psf-kicker">Live floor</p>
<h1>Shop floor</h1>
<p class="psf-lede"><?php echo esc_html( PSF_Plugin::setting( 'intro' ) ); ?></p>

<?php if ( ! $jobs ) : ?>
	<div class="psf-empty">
		<h2>The floor is idle</h2>
		<p>No made-to-order jobs are in the shop right now. When an order is paid, it will show up at the first station.</p>
	</div>
<?php else : ?>
	<p class="psf-count"><?php echo esc_html( sprintf( _n( '%s job on the floor', '%s jobs on the floor', count( $jobs ), 'public-shop-floor' ), number_format_i18n( count( $jobs ) ) ) ); ?></p>
	<div class="psf-board">
		<?php foreach ( $stations as $station ) : ?>
			<section class="psf-station">
				<h2><?php echo esc_html( $station['label'] ); ?></h2>
				<?php
				$column = PSF_Jobs::at_station( $station['key'] );
				if ( ! $column ) :
					?>
					<p class="psf-idle">Idle</p>
				<?php else : ?>
					<ol class="psf-queue">
						<?php foreach ( $column as $i => $job ) : ?>
							<li class="<?php echo 'held' === $job['status'] ? 'is-held' : ''; ?>">
								<a href="<?php echo esc_url( home_url( '/shop-floor/job/' . $job['token'] . '/' ) ); ?>">
									<span class="psf-no"><?php echo esc_html( $job['job_number'] ); ?></span>
									<?php if ( $show_product ) : ?>
										<span class="psf-prod"><?php echo esc_html( PSF_Jobs::product_title( $job ) ); ?></span>
									<?php endif; ?>
									<span class="psf-age"><?php echo 'held' === $job['status'] ? 'Held' : esc_html( PSF_Jobs::age_label( $job ) ); ?></span>
									<?php if ( 'held' !== $job['status'] ) : ?>
										<span class="psf-place"><?php echo 0 === $i ? 'At the bench' : esc_html( sprintf( '%d ahead', $i ) ); ?></span>
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

<?php
require PSF_DIR . 'templates/footer.php';
