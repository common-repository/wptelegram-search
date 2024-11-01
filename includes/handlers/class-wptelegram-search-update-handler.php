<?php

/**
 * Webhook Update Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * The Webhook Update Handling functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Update_Handler {

	/**
	 * The update object
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array 		$update
	 */
	private $update;

	/**
	 * The Telegram API
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var WPTelegram_Bot_API $tg_api Telegram API Object
	 */
	private $tg_api;

	/**
	 * Initialize the class and set its properties.
	 * @since    1.0.0
	 *
	 * @param	string	$bot_token Telegram Bot Token
	 */
	public function __construct( $bot_token ) {
		
		$this->tg_api = new WPTelegram_Bot_API( $bot_token );
	}

	/**
	 * set update
	 *
	 * @since  1.0.0
	 *
	 */
	public function set_update( $update ) {
		$this->update = $update;
	}

    /**
	 * Returns the Telegram Update
	 *
	 * @since  1.0.0
     *
     * @return array 
     */
    public function get_update() {
        return $this->update;
    }

	/**
	 * Process the update
	 *
	 * @since  1.0.0
	 *
	 */
	public function process() {
		
		if ( isset( $this->update['message'] ) ) {

			$handle = new Wptelegram_Search_Message_Handler( $this->update['message'], $this->tg_api, $this->update );

			$res = $handle->process();

		} elseif ( isset( $this->update['inline_query'] ) ) {

			$handle = new Wptelegram_Search_Inline_Handler( $this->update['inline_query'], $this->tg_api, $this->update );

			$res = $handle->process();

		} else{
			// Execute the action hook for other update types "channel_post", "callback_query" etc.
			do_action( 'wptelegram_search_handle_update', $this->update, $this->tg_api );
		}
	}
}