<?php
/**
 * CirclePay
 *
 * @package       CIRCLEPAY
 * @author        Mohamed Yassin
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   CirclePay
 * Plugin URI:    https://circlepay.ai
 * Description:   CirclePay provides Businesses with Multiple payment Solutions through single API integration.
 * Version:       1.0.0
 * Author:        Mohamed Yassin
 * Author URI:    https://github.com/mohamedyassin07
 * Text Domain:   circlepay
 * Domain Path:   /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Exit if WooCommerce is not installed or being update.
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

// Plugin name
define( 'CIRCLEPAY_NAME', 'CirclePay' );

// Plugin slug
define( 'CIRCLEPAY_SLUG', 'circlepay' );

// Plugin version
define( 'CIRCLEPAY_VERSION', '1.0.0' );

// Plugin Root File
define( 'CIRCLEPAY_PLUGIN_FILE', __FILE__ );

// Plugin base
define( 'CIRCLEPAY_PLUGIN_BASE', plugin_basename( CIRCLEPAY_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'CIRCLEPAY_PLUGIN_DIR',	plugin_dir_path( CIRCLEPAY_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'CIRCLEPAY_PLUGIN_URL',	plugin_dir_url( CIRCLEPAY_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once CIRCLEPAY_PLUGIN_DIR . 'core/class-circlepay.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Mohamed Yassin
 * @since   1.0.0
 * @return  object|Circlepay
 */
function CIRCLEPAY() {
	return Circlepay::instance();
}

CIRCLEPAY();