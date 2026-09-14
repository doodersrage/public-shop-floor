<?php
/**
 * Public board and per-job view.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Storefront board and job ticket routes.
 */
class PSFloor_Frontend {

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_filter( 'template_include', array( __CLASS__, 'template' ) );
		add_filter( 'document_title_parts', array( __CLASS__, 'title' ) );
		add_filter( 'redirect_canonical', array( __CLASS__, 'stop_canonical' ), 10, 2 );
	}

	/**
	 * Register custom query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'psf_board';
		$vars[] = 'psf_job';
		return $vars;
	}

	/**
	 * Whether the public board is requested.
	 *
	 * @return bool
	 */
	public static function is_board() {
		return (bool) get_query_var( 'psf_board' );
	}

	/**
	 * Job token from the request, if any.
	 *
	 * @return string
	 */
	public static function job_token() {
		return sanitize_text_field( (string) get_query_var( 'psf_job' ) );
	}

	/**
	 * Whether any shop-floor front view is active.
	 *
	 * @return bool
	 */
	public static function is_floor() {
		return self::is_board() || (bool) self::job_token();
	}

	/**
	 * Enqueue floor styles and drop theme/Woo chrome on floor views.
	 */
	public static function assets() {
		if ( ! self::is_floor() ) {
			return;
		}
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_enqueue_style( 'psf-floor', PSFLOOR_URL . 'assets/css/floor.css', array(), PSFLOOR_VERSION );
	}

	/**
	 * Swap in floor templates.
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public static function template( $template ) {
		if ( self::job_token() ) {
			self::render_job();
			exit;
		}
		if ( self::is_board() ) {
			self::render_board();
			exit;
		}
		return $template;
	}

	/**
	 * Render the public board (include runs in local scope).
	 */
	public static function render_board() {
		if ( '1' !== PSFloor_Plugin::setting( 'public_board', '1' ) ) {
			wp_die(
				esc_html__( 'The shop floor is not public.', 'public-shop-floor' ),
				esc_html__( 'Shop floor', 'public-shop-floor' ),
				array( 'response' => 403 )
			);
		}

		$stations     = PSFloor_Stations::all();
		$show_product = '1' === PSFloor_Plugin::setting( 'show_product', '1' );
		$jobs         = PSFloor_Jobs::on_floor();
		$shop         = PSFloor_Plugin::setting( 'shop_name', get_bloginfo( 'name' ) );

		include PSFLOOR_DIR . 'templates/header.php';
		include PSFLOOR_DIR . 'templates/board.php';
		include PSFLOOR_DIR . 'templates/footer.php';
	}

	/**
	 * Render a single job ticket.
	 */
	public static function render_job() {
		$token = self::job_token();
		$job   = $token ? PSFloor_Jobs::get_by_token( $token ) : null;
		$shop  = PSFloor_Plugin::setting( 'shop_name', get_bloginfo( 'name' ) );

		$station = null;
		$ahead   = 0;
		$done    = false;
		$held    = false;

		if ( $job ) {
			$station = PSFloor_Stations::get( $job['station_key'] );
			$ahead   = PSFloor_Jobs::place_in_line( $job );
			$done    = 'complete' === $job['status'];
			$held    = 'held' === $job['status'];
		}

		include PSFLOOR_DIR . 'templates/header.php';
		include PSFLOOR_DIR . 'templates/job.php';
		include PSFLOOR_DIR . 'templates/footer.php';
	}

	/**
	 * Document title for floor views.
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public static function title( $parts ) {
		if ( self::is_board() ) {
			$parts['title'] = __( 'Shop floor', 'public-shop-floor' );
		}
		if ( self::job_token() ) {
			$parts['title'] = __( 'Job', 'public-shop-floor' );
		}
		return $parts;
	}

	/**
	 * Prevent canonical redirects from breaking floor URLs.
	 *
	 * @param string|false $redirect  Redirect URL.
	 * @param string       $requested Requested URL.
	 * @return string|false
	 */
	public static function stop_canonical( $redirect, $requested ) {
		unset( $requested );
		if ( self::is_floor() ) {
			return false;
		}
		return $redirect;
	}
}
