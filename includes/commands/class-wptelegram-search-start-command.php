<?php

/**
 * Handles /start command
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * Handles /start command
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Start_Command extends Wptelegram_Search_Command {

	/**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var array Command Aliases
     */
    protected $aliases = array( 'hi', 'hello' );

    /**
     * @var string Command Description
     */
    protected $description = 'Start Command';

    /**
     * 
     */
    public function handle( $arguments ) {
        global $wptelegram_options;

        $text = json_decode( $wptelegram_options['search_msg']['start_reply'] );
        if ( ! $text ) {
            return;
        }
        $update = $this->get_update();
        if ( isset( $update['message']['from']['first_name'] ) ) {
            $text = str_replace( '{first_name}', $update['message']['from']['first_name'], $text );
        }
        $parse_mode = $wptelegram_options['search_msg']['parse_mode'];
        if ( 'none' == $parse_mode ) {
            unset( $parse_mode );
        }
        
        $responses[] = array(
            'Message' => compact( 'text', 'parse_mode' ),
        );

        $responses = (array) apply_filters(
            'wptelegram_search_response_to_start_command',
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