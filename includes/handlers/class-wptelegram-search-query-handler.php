<?php

/**
 * Query Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * Query Handling functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Query_Handler {

	/**
	 * Mode of query (text, command or inline)
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	sting	$mode
	 */
	private static $mode = 'text';

	/**
	 * Search keyword(s)
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	sting	$s
	 */
	private static $s = '';

	/**
	 * @since	1.0.0
	 *
	 * @param	string	$mode	Mode of query
	 */
	public function set_mode( $mode ) {
		self::$mode = $mode;
	}

	/**
	 * @since	1.0.0
	 *
	 * @param	string	$s	Search keyword(s)
	 */
	public function set_keywords( $s ) {
		self::$s = $s;
	}

    /**
	 * Get posts based on the query
	 *
	 * @since  1.0.0
	 *
     * @return array 
     */
    public function get_posts() {
        $args = self::get_query_args();

        // fetch posts
        $posts = get_posts( $args );

        return apply_filters( 'wptelegram_search_posts', $posts, $args );
    }

    /**
	 * Get the arguments for the query
	 *
	 * @since  1.0.0
	 *
     * @return array
     */
    public static function get_query_args() {
        global $wptelegram_options;

	    $args = array();

	    // set post type
	    $args['post_type'] = $wptelegram_options['search_wp']['which_post_type'];

	    // set taxonomy related query data
	    $from_terms = $wptelegram_options['search_wp']['from_terms'][0];
	    if ( 'all' != $from_terms ) {
			$terms = $wptelegram_options['search_wp']['terms'];

			$operator = 'IN';
			
			if ( 'not_selected' == $from_terms ) {
				$operator = 'NOT IN';
			}
			$args['tax_query'] = self::build_tax_query( $terms, $operator );
		}

	    // set author related query data
	    $from_authors = $wptelegram_options['search_wp']['from_authors'][0];
	    if ( 'all' != $from_authors ) {
			$authors = $wptelegram_options['search_wp']['authors'];

			if ( 'selected' == $from_authors ) {
				$args['author__in'] = $authors;
			} elseif ( 'not_selected' == $from_authors ) {
				$args['author__not_in'] = $authors;
			}
		}

		// set search keyword(s)
		$text_reply = $wptelegram_options['search_msg']['text_reply'][0];
		// Number of posts to get
		$num_posts = (int) $wptelegram_options['search_msg']['num_posts'];

		if ( self::$s ) {
			if ( 'command' == self::$mode || ( 'text' == self::$mode && 'search_results' == $text_reply ) ) {
				$args['s'] = self::$s;
			} elseif ( 'inline' == self::$mode ) {
				$args['s'] = self::$s;
				$num_posts = 50;
			}
		}
		$args['posts_per_page'] = $num_posts;

		return apply_filters( 'wptelegram_search_query_args', $args, self::$mode, self::$s );
    }

    /**
	 * Generate the tax_query
	 *
	 * @since  1.0.0
     *
	 * @param	array	$terms		Taxonomy Terms
	 * @param	string	$operator	Operator to test
	 *
     * @return array 
     */
    public static function build_tax_query( $terms, $operator = 'IN' ) {

    	$tax_query = array();

        foreach ( (array) $terms as $term ) {
        	list( $term_id, $taxonomy ) = explode( '@', $term );
        	// save terms in an array with taxonomy as key
        	$tax_terms[ $taxonomy ][] = $term_id;
        }

        foreach ( $tax_terms as $taxonomy => $terms ) {
        	$tax_query[] = compact( 'taxonomy', 'terms', 'operator' );
        }
        return $tax_query;
    }
}