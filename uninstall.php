<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://t.me/ManzoorWaniJK
 * @since      1.0.0
 *
 * @package    Wptelegram_Search
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
require_once plugin_dir_path( __FILE__ ) . 'includes/wptelegram-search-functions.php';

wptelegram_search_handle_uninstall();