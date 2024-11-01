<?php

/**
 * Handles /search command
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * Handles /search command
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Search_Command extends Wptelegram_Search_Command {

	/**
     * @var string Command Name
     */
    protected $name = 'search';

    /**
     * @var array Command Aliases
     */
    protected $aliases = array( 's', 'find' );

    /**
     * @var string Command Description
     */
    protected $description = 'Search';

    /**
     * Array of responses to be sent to the user
     *
     * @since   1.0.0
     * @access  private
     * @var     array   $responses
     */
    private $responses = array();

    /**
     * 
     */
    public function handle( $arguments ) {
        global $wptelegram_options;
        $text = $this->get_arguments();
        if ( ! $text ) {
            $text = __( 'Please send the /search command along with the text to be searched.', 'wptelegram' ) . ' ' . __( 'For example /search <query>', 'wptelegram' );
            $this->reply_with_message(
                compact( 'text' )
            );
            return;
        }
        
        $handle = new Wptelegram_Search_Query_Handler();

        // set query mode
        $handle->set_mode( 'command' );

        // set the search keyword
        $handle->set_keywords( $text );

        // get the posts
        $posts = $handle->get_posts();

        foreach ( $posts as $post ) {
            $this->add_response( $post );
        }
        $this->respond();
    }

    /**
     * Add a response according to the post
     *
     * @since  1.0.0
     *
     */
    public function add_response( $post ) {
        global $wptelegram_options;
        $text = Wptelegram_Search_Text_Handler::get_text( $post );
        if ( false === $text ) {
            return;
        }
        $parse_mode = $wptelegram_options['search_msg']['parse_mode'];
        $disable_web_page_preview = (bool) $wptelegram_options['search_msg']['misc']['disable_web_page_preview'];
        //$disable_notification = (bool) $wptelegram_options['search_msg']['misc']['disable_notification'];
        if ( 'none' == $parse_mode ) {
            unset( $parse_mode );
        }
        $this->responses[] = array(
            'sendMessage' => compact(
                'text',
                'parse_mode',
                'disable_web_page_preview',
                'disable_notification'
            ),
        );
    }

    /**
     * Respond to the message
     *
     * @since   1.0.0
     * @access  private
     *
     */
    private function respond() {
        if ( ! empty( $this->responses ) ) {
            $update = $this->get_update();
            $chat_id = $update['message']['chat']['id'];
            foreach ( $this->responses as $response ) {
                if ( ! empty( $response ) ) {
                    foreach ( $response as $method => $params ) {
                        $params['chat_id'] = $chat_id;
                        $res = call_user_func(
                            array(
                                $this->get_command_handler()->get_tg_api(),
                                $method
                            ),
                            $params
                        );
                    }
                }
            }
        }
    }
}