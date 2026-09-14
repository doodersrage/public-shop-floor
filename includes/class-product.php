<?php
/**
 * Product flag: made on the floor.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSFloor_Product {

	const META = '_psf_on_floor';

	public static function init() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );
	}

	public static function is_on_floor( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}
		if ( ! $product ) {
			return false;
		}
		return 'yes' === $product->get_meta( self::META );
	}

	public static function field() {
		woocommerce_wp_checkbox(
			array(
				'id'          => self::META,
				'label'       => __( 'Made on the floor', 'public-shop-floor' ),
				'description' => __( 'When this sells, a job appears on the public shop floor and moves station by station.', 'public-shop-floor' ),
			)
		);
	}

	public static function save( $product ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce product save already verified.
		$value = isset( $_POST[ self::META ] ) ? 'yes' : 'no';
		$product->update_meta_data( self::META, $value );
	}
}
