<?php
/**
 * Plugin Name: Public Shop Floor
 * Description: Made-to-order jobs on a real floor. Customers see station and place in line — not a fake tracking page.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Author: Public Shop Floor
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: public-shop-floor
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
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>Public Shop Floor needs WooCommerce to be installed and active.</p></div>';
				}
			);
			return;
		}
		PSF_Plugin::init();
	}
);
