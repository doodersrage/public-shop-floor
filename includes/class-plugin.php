<?php
/**
 * Bootstrap, settings, rewrite flush.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSF_Plugin {

	const OPTION = 'psf_settings';

	public static function init() {
		PSF_Jobs::maybe_install();
		add_action( 'init', array( __CLASS__, 'rewrites' ) );
		add_action( 'init', array( __CLASS__, 'maybe_flush' ) );
		PSF_Product::init();
		PSF_Orders::init();
		PSF_Admin::init();
		PSF_Frontend::init();
	}

	public static function activate() {
		PSF_Jobs::install();
		self::rewrites();
		update_option( 'psf_flush_rewrites', '1' );
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function rewrites() {
		add_rewrite_rule( '^shop-floor/?$', 'index.php?psf_board=1', 'top' );
		add_rewrite_rule( '^shop-floor/job/([a-zA-Z0-9]+)/?$', 'index.php?psf_job=$matches[1]', 'top' );
		add_rewrite_tag( '%psf_board%', '1' );
		add_rewrite_tag( '%psf_job%', '([a-zA-Z0-9]+)' );
	}

	public static function maybe_flush() {
		if ( '1' === get_option( 'psf_flush_rewrites' ) ) {
			flush_rewrite_rules();
			delete_option( 'psf_flush_rewrites' );
		}
	}

	public static function defaults() {
		return array(
			'shop_name'    => get_bloginfo( 'name' ),
			'intro'        => 'This is the floor, not a tracking page. Jobs move when a person finishes a station. If your piece is not on the board, it is not in the shop yet — or it has already left Packed.',
			'public_board' => '1',
			'show_product' => '1',
		);
	}

	public static function settings() {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::defaults(), $stored );
	}

	public static function setting( $key, $fallback = '' ) {
		$s = self::settings();
		return ( isset( $s[ $key ] ) && '' !== $s[ $key ] ) ? $s[ $key ] : $fallback;
	}
}
