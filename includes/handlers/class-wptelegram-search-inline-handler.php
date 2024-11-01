<?php

/**
 * Inline Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 */

/**
 * Inline Handling functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Inline_Handler {

	/**
	 * inline_query of an update
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array 		$inline_query
	 */
	private $inline_query;

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
	 * Article Results
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array	$results
	 */
	private $results = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param	array							$message Message from an update
	 * @param	WPTelegram_Bot_API	$tg_api Telegram API Object
	 * @param	array							$update The update
	 * @since	1.0.0
	 */
	public function __construct( $inline_query, $tg_api, $update ) {
		$this->inline_query = $inline_query;
		$this->tg_api = $tg_api;
		$this->update = $update;
	}

	/**
	 * Process the message
	 *
	 * @since  1.0.0
	 *
	 */
	public function process() {

		$handle = new Wptelegram_Search_Query_Handler();

    	// set query mode
    	$handle->set_mode( 'inline' );

    	// set the search keyword
    	$handle->set_keywords( $this->get_query() );

    	// get the posts
		$posts = $handle->get_posts();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$this->add_result( $post );
			}
		}
        $this->respond();
	}

	/**
	 * Get inline_query_id
	 *
	 * @since  1.0.0
	 *
	 */
	public function get_id() {
		return $this->inline_query['id'];
	}

	/**
	 * Get query text
	 *
	 * @since  1.0.0
	 *
	 */
	public function get_query() {
		return $this->inline_query['query'];
	}

	/**
	 * Get offset
	 *
	 * @since  1.0.0
	 *
	 */
	public function get_offset() {
		return $this->inline_query['offset'];
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
	 * Returnsinline_query
	 *
	 * @since  1.0.0
     *
     * @return array 
     */
    public function get_inline_query() {
        return $this->inline_query;
    }

	/**
	 * Add a result according to the post
	 *
	 * @since  1.0.0
	 *
	 */
	public function add_result( $post ) {
        global $wptelegram_options;
		$message_text = Wptelegram_Search_Text_Handler::get_text( $post );
		if ( false === $message_text ) {
			return;
		}
		if ( has_post_thumbnail( $post->ID ) ) {
			// post thumbnail ID
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			$thumb_url = wp_get_attachment_url( $thumbnail_id );
			$thumb_url = apply_filters( 'wptelegram_search_thumb_url', $thumb_url, $post );
		}
		else{
			$thumb_url = $wptelegram_options['search_msg']['thumb_url'];
		}
		$parse_mode = $wptelegram_options['search_msg']['parse_mode'];
        if ( 'none' == $parse_mode ) {
            unset( $parse_mode );
        }
		$disable_web_page_preview = (bool) $wptelegram_options['search_msg']['misc']['disable_web_page_preview'];

		$this->results[] = apply_filters(
			'wptelegram_search_inline_post_result',
			array(
			    'type'					=> 'article',
			    'id'					=> "$post->ID",
			    'title'					=> html_entity_decode( $post->post_title ),
			    'input_message_content'	=> compact(
			    	'message_text',
			    	'parse_mode',
			    	'disable_web_page_preview'
		    	),
		    	'url'					=> get_permalink( $post ),
			    
			    'description'			=> get_bloginfo( 'name' ),
			    'thumb_url'				=> $thumb_url,
			),
			$post,
			$this
		);
	}

	/**
	 * Respond to the message
	 *
	 * @since	1.0.0
	 * @access	private
	 *
	 */
	private function respond() {
		if ( ! empty( $this->results ) ) {
	        global $wptelegram_options;
			$cache_time = $wptelegram_options['search_msg']['cache_time'];

			$results = json_encode( $this->results );
			$inline_query_id = $this->get_id();

			$params = apply_filters(
				'wptelegram_search_answer_inline_query_params',
				compact(
					'inline_query_id',
					'results',
					'cache_time'
				),
				$this
			);
			$this->tg_api->answerInlineQuery( $params );
		}
	}
}