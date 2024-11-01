<?php

/**
 * Text Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * Text Handling functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Text_Handler {

	/**
	 * Text of the message
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	sting	$text
	 */
	private $text;

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
	 * Array of responses to be sent to the user
	 *
	 * @since  	1.0.0
	 * @access 	public
	 * @var  	array	$responses
	 */
	public $responses = array();

	/**
	 * Initialize the class and set its properties.
	 * @since	1.0.0
	 *
	 * @param	string					$text	Text of the message
	 * @param	WPTelegram_Bot_API	$tg_api Telegram API Object
	 * @param	array					$update	The update
	 */
	public function __construct( $text, $tg_api, $update ) {
		$this->text = $text;
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
     * Process the command
     *
     * @since	1.0.0
     *
     */
    public function process_text() {
    	
    	$reply_to_non_private_text = apply_filters( 'wptelegram_search_reply_to_non_private_text', false );

    	if ( isset( $this->update['message']['chat']['type'] ) && 'private' != $this->update['message']['chat']['type'] && ! $reply_to_non_private_text ) {
    		return;
    	}
        global $wptelegram_options;

        $text_reply = $wptelegram_options['search_msg']['text_reply'][0];
        if ( 'composed_message' == $text_reply ) {
        	$composed_message = $wptelegram_options['search_msg']['composed_message'];
        	$text = json_decode( $composed_message );
        	$parse_mode = $wptelegram_options['search_msg']['parse_mode'];
	        if ( 'none' == $parse_mode ) {
	            unset( $parse_mode );
	        }
        	$this->responses[] = array(
        		'sendMessage' => compact( 'text', 'parse_mode' ),
    		);
        } else {

        	$handle = new Wptelegram_Search_Query_Handler();
        	// set the search keyword
        	$handle->set_keywords( $this->text );
        	// get the posts
			$posts = $handle->get_posts();
			if ( empty( $posts ) ) {
				$text = __( 'No results found', 'wptelegram' );
				$this->responses[] = apply_filters(
					'wptelegram_search_response_to_no_results_found',
					array(
			    		'sendMessage' => compact( 'text' ),
					)
				);
			} else {
				foreach ( $posts as $post ) {
					$this->add_response( $post );
				}
			}
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
		$text = self::get_text( $post );
		if ( false === $text ) {
			return;
		}
		$parse_mode = $wptelegram_options['search_msg']['parse_mode'];
		$disable_web_page_preview = (bool) $wptelegram_options['search_msg']['misc']['disable_web_page_preview'];
		$disable_notification = (bool) $wptelegram_options['search_msg']['misc']['disable_notification'];
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
	 * Prepare Message
	 *
	 * @since	1.0.0
	 *
	 * @param	$post	WP_Post
	 */
	public static function get_text( $post ) {
        global $wptelegram_options;

        $template = $wptelegram_options['search_msg']['result_template'];
        if ( ! $template ) {
        	return false;
        }
        $template = json_decode( $template );
		$template = apply_filters( 'wptelegram_search_result_template', $template, $post);

        $excerpt_source = $wptelegram_options['search_msg']['excerpt_source'];
		$excerpt_length = $wptelegram_options['search_msg']['excerpt_length'];
		$parse_mode = $wptelegram_options['search_msg']['parse_mode'];

		if( 'post' == $post->post_type || 'page' == $post->post_type ) {
			$tags_arr = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
			$cats_arr = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );	
		}
		elseif( 'product' == $post->post_type ){
			$pf = new WC_Product_Factory();
			$product = $pf->get_product( $post->ID );
			$tags_arr = explode( ', ' , $product->get_tags() );
			$tags_arr = array_map( 'strip_tags', $tags_arr );

			$cats_arr = explode( ', ' , $product->get_categories() );
			$cats_arr = array_map( 'strip_tags', $cats_arr );
		}
		else{
			$tags_arr = apply_filters( 'wptelegram_cpt_tags', array(), $post );
			if ( ! is_array( $tags_arr ) ) {
				$tags_arr = array();
			}
			$cats_arr = apply_filters( 'wptelegram_cpt_cats', array(), $post );
			if ( ! is_array( $cats_arr ) ) {
				$cats_arr = array();
			}
		}
		
		$tags = ( ! empty( $tags_arr ) && '' != $tags_arr[0] ) ? '#' . implode( ' #', $tags_arr ) : '';

		$cats = ( ! empty( $cats_arr ) && '' != $cats_arr[0] ) ? implode( '|', $cats_arr ) : '';

		if ( 'before_more' == $excerpt_source ) {
			$parts = get_extended ( $post->post_content );
			$excerpt = $parts['main'];
		} else {
			$excerpt = wp_trim_words( $post->$excerpt_source, $excerpt_length, '...' );
		}
		
		$author = get_the_author_meta( 'display_name', $post->post_author );
		$content = trim( strip_tags( html_entity_decode( $post->post_content ), '<b><strong><em><i><a><pre><code>' ) );

		$macro_values = array(
			'{ID}'			=>	$post->ID,
			'{title}'		=>	$post->post_title,
			'{excerpt}'		=>	$excerpt,
			'{content}'		=>	$content,
			'{author}'		=>	$author,
			'{short_url}'	=>	wp_get_shortlink( $post->ID ),
			'{full_url}'	=>	get_permalink( $post->ID ),
			'{tags}'		=>	$tags,
			'{categories}'	=>	$cats,
		);

		/**
         * Use this filter to replace your own macros
         * with the corresponding values
         */
		$macro_values = (array) apply_filters( 'wptelegram_macro_values', $macro_values, $post );

		$markdown_search = array( '_', '*', '[' );
		$markdown_replace = array( '\_', '\*', '\[' );

		$text = $template;

		foreach ( $macro_values as $macro => $macro_value ) {
			if( 'Markdown' == $parse_mode ){
				$macro_value = str_replace( $markdown_search, $markdown_replace, $macro_value );
			}
			$text = str_replace( $macro, $macro_value, $text );
		}

		// replace taxonomy with its terms from the post
		if ( preg_match_all( '/(?<=\{\[)[a-z_]+?(?=\]\})/iu', $text, $matches ) ) {
			foreach ( $matches[0] as $taxonomy ) {
				$replace = '';
				if ( taxonomy_exists( $taxonomy ) ) {
					$terms = get_the_terms( $post->ID, $taxonomy );
					if ( ! empty( $terms ) ) {
						$names = array();
						foreach ( $terms as $term ) {
							$name = $term->name;
							if ( 'Markdown' == $parse_mode ) {
								$name = str_replace( $markdown_search, $markdown_replace, $name );
							}
							$names[] = $name;
						}
						if ( is_taxonomy_hierarchical( $taxonomy ) ) {
							$replace = implode( ' | ', $names );
						}
						else{
							$replace = '#'.implode( ' #', $names );
						}
					}
				}
				$replace = apply_filters( 'wptelegram_replace_macro_taxonomy', $replace, $taxonomy, $post );

				$text = str_replace( '{['.$taxonomy.']}', $replace, $text );
			}
		}

		// replace custom fields with their values
		if ( preg_match_all( '/(?<=\{\[\[).+?(?=\]\]\})/u', $text, $matches ) ) {
			foreach ( $matches[0] as $meta_key ) {
				$meta_value = (string) get_post_meta( $post->ID, $meta_key, true );

				$meta_value = apply_filters( 'wptelegram_replace_macro_custom_field', $meta_value, $meta_key, $post );
				
				if ( 'Markdown' == $parse_mode ) {
					$meta_value = str_replace( $markdown_search, $markdown_replace, $meta_value );
				}
				$text = str_replace( '{[['.$meta_key.']]}', $meta_value, $text );
			}
		}
		$text = html_entity_decode( $text );
		if ( 'Markdown' != $parse_mode ) {
			$text = stripslashes( $text );
		}
		
		return self::filter_text( $text, $parse_mode );
	}

	/**
	 * Filter Text
	 *
	 * @since 1.0.0
	 *
	 * @param $text 	  string
	 * @param $parse_mode string
     *
     * @return string
	 */
	private static function filter_text( $text, $parse_mode ){
		if ( 'HTML' == $parse_mode ) {
			// remove unnecessary tags
			$text = strip_tags( $text, '<b><strong><em><i><a><pre><code>' );

			// remove <em> if <a> is nested in it
			$pattern = '#(<em>)((.+)?<a\s+(?:[^>]*?\s+)?href=["\']?([^\'"]*)["\']?.*?>(.*?)<\/a>(.+)?)(<\/em>)#iu';
			$text = preg_replace( $pattern, '$2', $text);

			// remove <strong> if <a> is nested in it
			$pattern = '#(<strong>)((.+)?<a\s+(?:[^>]*?\s+)?href=["\']?([^\'"]*)["\']?.*?>(.*?)<\/a>(.+)?)(<\/strong>)#iu';
			$text = preg_replace( $pattern, '$2', $text );

			// remove <b> if <a> is nested in it
			$pattern = '#(<b>)((.+)?<a\s+(?:[^>]*?\s+)?href=["\']?([^\'"]*)["\']?.*?>(.*?)<\/a>(.+)?)(<\/b>)#iu';
			$text = preg_replace( $pattern, '$2', $text );

			// remove <i> if <a> is nested in it
			$pattern = '#(<i>)((.+)?<a\s+(?:[^>]*?\s+)?href=["\']?([^\'"]*)["\']?.*?>(.*?)<\/a>(.+)?)(<\/i>)#iu';
			$text = preg_replace( $pattern, '$2', $text );

			$text = self::handle_html_chars( $text );
		}
		else{
			$text = strip_tags( $text );
			if ( 'Markdown' == $parse_mode ) {
				$text = preg_replace_callback( '/\*(.+?)\*/su', 'wptelegram_replace_nested_markdown', $text );
			}
		}
		return $text;
	}

    /**
     * Replace HTML special characters with their codes
	 *
	 * @since 1.0.1
	 *
     * @param $text string
     *
     * @return string
     */
    private static function handle_html_chars( $text ) {
        $pattern = '#(?:<\/?)(?:(?:a(?:[^<>]+?)?>)|(?:b>)|(?:strong>)|(?:i>)|(?:em>)|(?:pre>)|(?:code>))(*SKIP)(*FAIL)|[<>&]+#iu';
        
        $filtered = preg_replace_callback( $pattern, 'Wptelegram_Search_Text_Handler::get_htmlentities', $text );

        return $filtered;
    }

    /**
     * Convert the character into html code
	 *
	 * @since 1.0.0
     *
     * @param $match array
     *
     * @return string
     */
    private static function get_htmlentities( $match ) {
    	return htmlentities( $match[0] );
    }

	/**
	 * Respond to the message
	 *
	 * @since	1.0.0
	 * @access	private
	 *
	 */
	private function respond() {
		$chat_id = $this->update['message']['chat']['id'];
		if ( ! empty( $this->responses ) ) {
			foreach ( $this->responses as $response ) {
				if ( ! empty( $response ) ) {
					foreach ( $response as $method => $params ) {
						$params['chat_id'] = $chat_id;
						call_user_func( array( $this->tg_api, $method ), $params );
					}
				}
			}
		}
	}
}