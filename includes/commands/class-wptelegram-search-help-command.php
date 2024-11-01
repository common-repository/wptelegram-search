<?php

/**
 * Handles /help command
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * Handles /help command
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Help_Command extends Wptelegram_Search_Command {

	/**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var array Command Aliases
     */
    protected $aliases = array( 'listcommands' );

    /**
     * @var string Command Description
     */
    protected $description = 'Help command';

    /**
     * 
     */
    public function handle( $arguments ) {
        global $wptelegram_options;

        $text = json_decode( $wptelegram_options['search_msg']['help_reply'] );
        if ( ! $text ) {
            return;
        }
        $parse_mode = $wptelegram_options['search_msg']['parse_mode'];
        if ( 'none' == $parse_mode ) {
            unset( $parse_mode );
        }

        /**
         * Without filters, the response can be simple
         *
         * $this->reply_with_message(
         *   compact( 'text', 'parse_mode' )
         * );
         */

        $responses[] = array(
            'Message' => compact( 'text', 'parse_mode' ),
        );

        $responses = (array) apply_filters(
            'wptelegram_search_response_to_help_command',
            $responses,
            $arguments
        );

        if ( ! empty( $responses ) ) {
            foreach ( $responses as $response ) {
                if ( ! empty( $response ) ) {
                    foreach ( $response as $method => $params ) {
                        call_user_func( array( $this, 'reply_with_' . $method ), $params );
                    }
                }
            }
        }
    }
}