<?php

/**
 * Message Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 */

/**
 * Message Handling functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Message_Handler {

	/**
	 * Message of an update
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array 		$message
	 */
	private $message;

	/**
	 * The Telegram API
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var WPTelegram_Bot_API $tg_api Telegram API Object
	 */
	private $tg_api;

	/**
	 * The update object
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array 		$update
	 */
	private $update;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param	array							$message Message from an update
	 * @param	WPTelegram_Bot_API	$tg_api Telegram API Object
	 * @param	array							$update The update
	 * @since	1.0.0
	 */
	public function __construct( $message, $tg_api, $update ) {
		$this->message = $message;
		$this->tg_api = $tg_api;
		$this->update = $update;
	}

    /**
	 * Returns WPTelegram Telegram Api Object
	 *
	 * @since  1.0.0
     *
     * @return WPTelegram_Bot_API
     */
    public function get_tg_api() {
        return $this->tg_api;
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
	 * Returns the Telegram Update Message
	 *
	 * @since  1.0.0
     *
     * @return array 
     */
    public function get_message() {
        return $this->message;
    }

	/**
	 * Process the message
	 *
	 * @since  1.0.0
	 *
	 */
	public function process() {

		$unsupported_message = false;
		/**
		 * The types of messages that are addressed
		 * to the bot and may expect a response
		 * unlike other message types which
		 * do not necessarily expect a response
		 * e.g. "new_chat_members" etc.
		 */
		$unsupported_types = array(
			'audio',
			'document',
			'game',
			'photo',
			'sticker',
			'video',
			'voice',
			'video_note',
			'contact',
			'location',
			'venue',
		);
		if ( $this->is_of_type( $unsupported_types ) ) {
			$unsupported_message = true;
		}

		if ( $this->is_of_type( 'text' ) ) {
			$this->handle_text_message();
		} elseif ( $unsupported_message ) {
			$this->handle_unsupported_message();
		} else{
			// Execute the action hook for other message types like "new_chat_members" etc.
			do_action( 'wptelegram_search_handle_message', $this->message, $this->tg_api, $this->update );
		}
	}

	/**
	 * Handle the text message
	 *
	 * @since  1.0.0
	 *
	 */
	private function handle_text_message() {
		$text = $this->message['text'];
		if ( $this->is_command( $text ) ) {
			$this->handle_command( $text );
		} else{
			$this->handle_text( $text );
		}
	}

	/**
	 * Handle a command
	 *
	 * @since  1.0.0
	 *
	 */
	private function handle_command( $text ) {
		$handle = new Wptelegram_Search_Command_Handler( $text, $this->tg_api, $this->update );

		$handle->process_command();
	}

	/**
	 * Handle a non command text
	 *
	 * @since  1.0.0
	 *
	 */
	private function handle_text( $text ) {

		$handle = new Wptelegram_Search_Text_Handler( $text, $this->tg_api, $this->update );

		$handle->process_text();
	}

	/**
	 * Handle an unsupported message
	 *
	 * @since  1.0.0
	 *
	 */
	private function handle_unsupported_message() {
        global $wptelegram_options;

        $text = json_decode( $wptelegram_options['search_msg']['non_text_reply'] );
        if ( ! $text ) {
            return;
        }
        $parse_mode = $wptelegram_options['search_msg']['parse_mode'];
        if ( 'none' == $parse_mode ) {
            unset( $parse_mode );
        }
        $chat_id = $this->get_message()['chat']['id'];
        $params = apply_filters(
        	'wptelegram_search_non_text_reply_params',
        	compact( 'text', 'chat_id', 'parse_mode' )
    	);
        
        $this->get_tg_api()->sendMessage( $params );
	}

	/**
	 * If message has the object of the given type
	 *
	 * @since  1.0.0
	 *
	 * @param	array|string	$type Type of message
	 */
	private function is_of_type( $type ) {
		if ( is_array( $type ) ) {
			foreach ( $type as $object ) {
				if ( isset( $this->message[ $object ] ) ) {
					return true;
				}
			}
		} elseif ( isset( $this->message[ $type ] ) ) {
			return true;
		}
		return false;
	}

	private function is_command( $text ) {
		/**
		 * Command pattern for private and group chats
		 * For example /help, /help@BotUsername
		 */
		$pattern = '/^\/[^\s@]+?(?:@[a-z]\w+)?/i';

		if ( preg_match( $pattern, $text ) ) {
			return true;
		}
		return false;
	}
}