<?php
/**
 * Plugin Name:       Public Shop Floor
 * Plugin URI:        https://wordpress.org/plugins/public-shop-floor/
 * Description:       Made-to-order jobs on a real floor. Customers see station and place in line — not a fake tracking page.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * WC requires at least: 8.2
 * WC tested up to:   11.1
 * Author:            Public Shop Floor
 * Author URI:        https://wordpress.org/plugins/public-shop-floor/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       public-shop-floor
 * Domain Path:       /languages
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PSF_VERSION', '1.0.0' );
define( 'PSF_FILE', __FILE__ );
define( 'PSF_DIR', plugin_dir_path( __FILE__ ) );
define( 'PSF_URL', plugin_dir_url( __FILE__ ) );

require_once PSF_DIR . 'includes/class-plugin.php';
require_once PSF_DIR . 'includes/class-stations.php';
require_once PSF_DIR . 'includes/class-jobs.php';
require_once PSF_DIR . 'includes/class-product.php';
require_once PSF_DIR . 'includes/class-orders.php';
require_once PSF_DIR . 'includes/class-admin.php';
require_once PSF_DIR . 'includes/class-frontend.php';

register_activation_hook( __FILE__, array( 'PSF_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PSF_Plugin', 'deactivate' ) );

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PSF_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', PSF_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'public-shop-floor', false, dirname( plugin_basename( PSF_FILE ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Public Shop Floor needs WooCommerce to be installed and active.', 'public-shop-floor' ) . '</p></div>';
				}
			);
			return;
		}
		PSF_Plugin::init();
	}
);
