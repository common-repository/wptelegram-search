<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://t.me/ManzoorWaniJK
 * @since             1.0.0
 * @package           Wptelegram_Search
 *
 * @wordpress-plugin
 * Plugin Name:       WP Telegram Search
 * Plugin URI:        https://t.me/WPTelegram
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.3
 * Author:            Manzoor Wani
 * Author URI:        https://t.me/ManzoorWaniJK
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wptelegram-search
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wptelegram-search-activator.php
 */
function activate_wptelegram_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wptelegram-search-activator.php';
	Wptelegram_Search_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wptelegram-search-deactivator.php
 */
function deactivate_wptelegram_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wptelegram-search-deactivator.php';
	Wptelegram_Search_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wptelegram_search' );
register_deactivation_hook( __FILE__, 'deactivate_wptelegram_search' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wptelegram-search.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wptelegram_search() {

	$plugin = new Wptelegram_Search();
	$plugin->run();

}

if ( ! defined( 'WPTELEGRAM_SEARCH_URL' ) ) {
    define( 'WPTELEGRAM_SEARCH_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
}
if ( ! defined( 'WPTELEGRAM_SEARCH_DIR' ) ) {
    define( 'WPTELEGRAM_SEARCH_DIR', untrailingslashit( dirname( __FILE__ ) ) );
}
if ( ! defined( 'WPTELEGRAM_SEARCH_VER' ) ) {
    define( 'WPTELEGRAM_SEARCH_VER', '1.0.3' );
}
run_wptelegram_search();
