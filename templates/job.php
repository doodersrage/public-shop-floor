<?php
/**
 * Single job ticket markup.
 *
 * Expects local scope vars from PSFloor_Frontend::render_job():
 * $token, $job, $shop, $station, $ahead, $done, $held.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $job ) :
	?>
	<div class="psf-empty">
		<h1><?php echo esc_html__( 'No job with that ticket', 'public-shop-floor' ); ?></h1>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: URL to the public shop floor */
					__( 'The link may be wrong, or the piece already left Packed. Check the <a href="%s">live floor</a>.', 'public-shop-floor' ),
					esc_url( home_url( '/shop-floor/' ) )
				)
			);
			?>
		</p>
	</div>
	<?php
	return;
endif;
?>

<p class="psf-kicker"><?php echo esc_html__( 'Job ticket', 'public-shop-floor' ); ?></p>
<p class="psf-no-lg"><?php echo esc_html( $job['job_number'] ); ?></p>
<h1><?php echo esc_html( PSFloor_Jobs::product_title( $job ) ); ?></h1>

<?php if ( $done ) : ?>
	<div class="psf-status is-done">
		<p><?php echo esc_html__( 'This job has left the floor. Packed is finished; it is no longer in a station queue.', 'public-shop-floor' ); ?></p>
	</div>
<?php elseif ( $held ) : ?>
	<div class="psf-status is-held">
		<p>
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: station label */
					__( 'Held at %s. The bench is waiting on a part, a question, or a finish to dry. It is not in line until someone resumes it.', 'public-shop-floor' ),
					$station ? $station['label'] : $job['station_key']
				)
			);
			?>
		</p>
	</div>
<?php else : ?>
	<div class="psf-status">
		<p class="psf-verb"><?php echo esc_html( $station ? $station['verb'] : $job['station_key'] ); ?></p>
		<p>
			<?php
			if ( 0 === $ahead ) {
				echo esc_html__( 'This piece is at the bench now.', 'public-shop-floor' );
			} else {
				echo esc_html(
					sprintf(
						/* translators: 1: number of jobs ahead, 2: station label */
						_n( '%1$d job ahead in %2$s.', '%1$d jobs ahead in %2$s.', $ahead, 'public-shop-floor' ),
						$ahead,
						$station ? $station['label'] : __( 'this station', 'public-shop-floor' )
					)
				);
			}
			?>
		</p>
		<p class="psf-age"><?php echo esc_html( PSFloor_Jobs::age_label( $job ) ); ?></p>
	</div>
<?php endif; ?>

<ol class="psf-pipeline" aria-label="<?php echo esc_attr__( 'Station pipeline', 'public-shop-floor' ); ?>">
	<?php foreach ( PSFloor_Stations::all() as $psf_step ) : ?>
		<?php
		$psf_keys  = PSFloor_Stations::keys();
		$psf_here  = array_search( $job['station_key'], $psf_keys, true );
		$psf_index = array_search( $psf_step['key'], $psf_keys, true );
		$psf_class = 'upcoming';
		if ( $done || ( false !== $psf_here && false !== $psf_index && $psf_index < $psf_here ) ) {
			$psf_class = 'past';
		}
		if ( ! $done && $psf_step['key'] === $job['station_key'] ) {
			$psf_class = 'now';
		}
		?>
		<li class="<?php echo esc_attr( $psf_class ); ?>">
			<span><?php echo esc_html( $psf_step['label'] ); ?></span>
		</li>
	<?php endforeach; ?>
</ol>

<p><a class="psf-text-link" href="<?php echo esc_url( home_url( '/shop-floor/' ) ); ?>"><?php echo esc_html__( 'See the whole floor', 'public-shop-floor' ); ?></a></p>
