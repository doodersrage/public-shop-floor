<?php
/**
 * Single job on the floor.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$token = PSF_Frontend::job_token();
$job   = $token ? PSF_Jobs::get_by_token( $token ) : null;

require PSF_DIR . 'templates/header.php';

if ( ! $job ) :
	?>
	<div class="psf-empty">
		<h1>No job with that ticket</h1>
		<p>The link may be wrong, or the piece already left Packed. Check the <a href="<?php echo esc_url( home_url( '/shop-floor/' ) ); ?>">live floor</a>.</p>
	</div>
	<?php
	require PSF_DIR . 'templates/footer.php';
	return;
endif;

$station = PSF_Stations::get( $job['station_key'] );
$ahead   = PSF_Jobs::place_in_line( $job );
$done    = 'complete' === $job['status'];
$held    = 'held' === $job['status'];
?>

<p class="psf-kicker">Job ticket</p>
<p class="psf-no-lg"><?php echo esc_html( $job['job_number'] ); ?></p>
<h1><?php echo esc_html( PSF_Jobs::product_title( $job ) ); ?></h1>

<?php if ( $done ) : ?>
	<div class="psf-status is-done">
		<p>This job has left the floor. Packed is finished; it is no longer in a station queue.</p>
	</div>
<?php elseif ( $held ) : ?>
	<div class="psf-status is-held">
		<p>Held at <?php echo esc_html( $station ? $station['label'] : $job['station_key'] ); ?>. The bench is waiting on a part, a question, or a finish to dry. It is not in line until someone resumes it.</p>
	</div>
<?php else : ?>
	<div class="psf-status">
		<p class="psf-verb"><?php echo esc_html( $station ? $station['verb'] : $job['station_key'] ); ?></p>
		<p>
			<?php
			if ( 0 === $ahead ) {
				echo 'This piece is at the bench now.';
			} else {
				echo esc_html( sprintf( _n( '%d job ahead in %s.', '%d jobs ahead in %s.', $ahead, 'public-shop-floor' ), $ahead, $station ? $station['label'] : 'this station' ) );
			}
			?>
		</p>
		<p class="psf-age"><?php echo esc_html( PSF_Jobs::age_label( $job ) ); ?></p>
	</div>
<?php endif; ?>

<ol class="psf-pipeline">
	<?php foreach ( PSF_Stations::all() as $step ) : ?>
		<?php
		$keys  = PSF_Stations::keys();
		$here  = array_search( $job['station_key'], $keys, true );
		$index = array_search( $step['key'], $keys, true );
		$class = 'upcoming';
		if ( $done || ( false !== $here && false !== $index && $index < $here ) ) {
			$class = 'past';
		}
		if ( ! $done && $step['key'] === $job['station_key'] ) {
			$class = 'now';
		}
		?>
		<li class="<?php echo esc_attr( $class ); ?>">
			<span><?php echo esc_html( $step['label'] ); ?></span>
		</li>
	<?php endforeach; ?>
</ol>

<p><a class="psf-text-link" href="<?php echo esc_url( home_url( '/shop-floor/' ) ); ?>">See the whole floor</a></p>

<?php
require PSF_DIR . 'templates/footer.php';
