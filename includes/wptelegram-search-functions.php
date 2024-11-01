<?php

/**
 * Settings Sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function wptelegram_search_option_sections( $sections = array(), $only_search = false ) {
	$new_sections = array(
		'search_tg',
		'search_wp',
		'search_msg',
	);
	if ( $only_search ) {
		return $new_sections;
	}
	return array_merge( (array) $sections, $new_sections );
}
add_filter( 'wptelegram_option_sections', 'wptelegram_search_option_sections', 10, 1 );

/**
 * Default Options
 *
 * @since  1.0.0
 *
 * @return array
 */
function wptelegram_search_default_options( $defaults = array() ) {
	$arr = array();
	$new_defaults = array(
		'search_tg' => array(
			'bot_token'	=> '',
		),
		'search_wp' => array(
			'which_post_type'	=> $arr,
			'from_terms'		=> 'all',
			'terms'				=> $arr,
			'from_authors'		=> 'all',
			'authors'			=> $arr,
		),
		'search_msg' => array(
			'start_reply'		=> '',
			'help_reply'		=> '',
			'non_text_reply'	=> '',
			'text_reply'		=> 'search_results',
			'num_posts'			=> 5,
			'composed_message'	=> '',
			'result_template'	=> '',
			'cache_time'		=> 300,
			'thumb_url'			=> '',
			'excerpt_source'	=> 'post_content',
			'excerpt_length'	=> 55,
			'parse_mode'		=> 'none',
			'misc'				=> array(
				'disable_web_page_preview'	=> false,
				'disable_notification'		=> false,
			),
		),
	);
	return array_merge( (array) $defaults, $new_defaults );
}
add_filter( 'wptelegram_default_options', 'wptelegram_search_default_options', 10, 1 );

/**
 * Sanitize cache_time
 *
 * @since  1.0.0
 *
 */
function wptelegram_sanitize_cache_time( $value ) {
    return filter_var(
	    $value,
	    FILTER_VALIDATE_INT, 
	    array(
	        'options' => array(
	            'min_range'	=> 1, 
	            'default'	=> 300,
	        )
	    )
	);
}

/**
 * Called when plugin in uninstalled
 *
 * @since  1.0.0
 *
 */
function wptelegram_search_handle_uninstall() {

	$options = get_option( 'wptelegram_search_wp' ) ;
	if ( isset( $options['remove_settings'] ) && 'off' == $options['remove_settings'] ) {
		return;
	}
	$sections = wptelegram_search_option_sections( array(), true );
	foreach ( $sections as $section ) {
		delete_option( 'wptelegram_' . $section );
	}
}