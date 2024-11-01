<?php

/**
 * Fired during plugin activation
 *
 * @link       https://t.me/ManzoorWaniJK
 * @since      1.0.0
 *
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/includes
 * @author     Manzoor Wani <manzoorwani.jk@gmail.com>
 */
class Wptelegram_Search_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! is_ssl() ) {
			// Deactivate the plugin
			deactivate_plugins( 'wptelegram-search/wptelegram-search.php' );
			
			// Throw an error in the wordpress admin console
			$error_message = '<b>' . __( 'WPTelegram Search', 'wptelegram' ) . '</b>&nbsp;' . __( 'requires SSL (https://) to set up webhook for receiving updates.', 'wptelegram' ) . '&nbsp;<a href="' . esc_url( 'https://core.telegram.org/bots/webhooks' ) . '" target="_blank">' . __( 'Learn more', 'wptelegram' ) . '</a>';
			wp_die( $error_message, __( 'Error' ), array( 'back_link' => true ) );
		}
	}
}
