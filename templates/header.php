<?php
/**
 * Shared floor chrome (header).
 *
 * Expects $shop from the renderer.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'psf-body' ); ?>>
<header class="psf-mast">
	<div class="psf-wrap">
		<p class="psf-shop"><a href="<?php echo esc_url( home_url( '/shop-floor/' ) ); ?>"><?php echo esc_html( $shop ); ?></a></p>
		<nav aria-label="<?php echo esc_attr__( 'Shop floor', 'public-shop-floor' ); ?>">
			<a href="<?php echo esc_url( home_url( '/shop-floor/' ) ); ?>"><?php echo esc_html__( 'Floor', 'public-shop-floor' ); ?></a>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html__( 'Shop', 'public-shop-floor' ); ?></a>
		</nav>
	</div>
</header>
<main class="psf-wrap psf-main" id="psf-main">
