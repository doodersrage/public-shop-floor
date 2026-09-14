<?php
/**
 * Public board and per-job view.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSF_Frontend {

	public static function init() {
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_filter( 'template_include', array( __CLASS__, 'template' ) );
		add_filter( 'document_title_parts', array( __CLASS__, 'title' ) );
		add_filter( 'redirect_canonical', array( __CLASS__, 'stop_canonical' ), 10, 2 );
	}

	public static function query_vars( $vars ) {
		$vars[] = 'psf_board';
		$vars[] = 'psf_job';
		return $vars;
	}

	public static function is_board() {
		return (bool) get_query_var( 'psf_board' );
	}

	public static function job_token() {
		return sanitize_text_field( (string) get_query_var( 'psf_job' ) );
	}

	public static function is_floor() {
		return self::is_board() || (bool) self::job_token();
	}

	public static function assets() {
		if ( ! self::is_floor() ) {
			return;
		}
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_enqueue_style( 'psf-floor', PSF_URL . 'assets/css/floor.css', array(), PSF_VERSION );
	}

	public static function template( $template ) {
		if ( self::job_token() ) {
			return PSF_DIR . 'templates/job.php';
		}
		if ( self::is_board() ) {
			return PSF_DIR . 'templates/board.php';
		}
		return $template;
	}

	public static function title( $parts ) {
		if ( self::is_board() ) {
			$parts['title'] = 'Shop floor';
		}
		if ( self::job_token() ) {
			$parts['title'] = 'Job';
		}
		return $parts;
	}

	public static function stop_canonical( $redirect, $requested ) {
		unset( $requested );
		if ( self::is_floor() ) {
			return false;
		}
		return $redirect;
	}
}
