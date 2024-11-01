<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/admin
 */

/**
 * The admin-specific settings of the plugin.
 *
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/admin
 * @author     Manzoor Wani <manzoorwani.jk@gmail.com>
 */
class Wptelegram_Search_Admin_Settings {

	/**
	 * Inbuilt and Registered Post Types
	 *
	 * @since  	1.2.0
	 * @access 	private
	 * @var array $post_types Post Types
	 */
	private $post_types;

    /**
     * The Telegram API
     *
     * @since   1.0.0
     * @access  private
     * @var WPTelegram_Bot_API $tg_api Telegram API Object
     */
    private $tg_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

	}

    /**
     * get all the settings sections
     *
     * @since    1.2.0
     * @return array settings fields
     */
    public function get_settings_sections() {
        $sections = array(
            array(
                'id'       => 'wptelegram_search_tg',
                'title'    => __( 'Telegram Settings', 'wptelegram' ),
                'callback' => array( $this, 'wptelegram_telegram_cb' ),
                'icon_src' => WPTELEGRAM_URL . '/admin/icons/telegram.svg',
            ),
            array(
                'id'       => 'wptelegram_search_wp',
                'title'    => __( 'WordPress Settings', 'wptelegram' ),
                'desc'     => __( 'In this section you can teach WordPress how and when to do the job', 'wptelegram' ),
                'icon_src' => WPTELEGRAM_URL . '/admin/icons/wordpress.svg',
            ),
            array(
                'id'       => 'wptelegram_search_msg',
                'title'    => __( 'Message Settings', 'wptelegram' ),
                'desc' => __( 'In this section you can change the way messages are sent to Telegram', 'wptelegram' ),
                'icon_src' => WPTELEGRAM_URL . '/admin/icons/message.svg',
            ),
        );
        return apply_filters( 'wptelegram_search_admin_settings_sections_array', $sections );
    }

    /**
     * get all the settings fields
	 *
	 * @since    1.2.0
     * @return array settings fields
     */
    public function get_settings_fields() {
        $settings_fields = array(
            'wptelegram_search_tg' => array(
                array(
                    'name'              => 'bot_token',
                    'label'             => __( 'Bot Token', 'wptelegram' ),
                    'desc'              => __( 'Please read the instructions above', 'wptelegram' ),
                    'placeholder'       => __( 'e.g.', 'wptelegram' ) . ' 123456789:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                    'type'              => 'text',
                    'sanitize_callback' => 'wptelegram_sanitize_bot_token',
                    'events'            => array(
                        'onblur' => 'validateToken("search_tg")',
                    ),
                    'button'            => array(
                        'name'  => __( "Test Token", 'wptelegram' ),
                        'id'    => 'checkbot',
                        'class' => 'button-secondary',
                        'events'=> array(
                            'onclick' => 'getMe("search_tg")',
                        ),
                    ),
                ),
                array(
                    'name'        => 'bot_html',
                    'desc'        => $this->bot_token_html_cb( 'search_tg' ),
                    'type'        => 'html'
                ),
                array(
                    'name'        => 'webhook',
                    'desc'        => $this->webhook_html_cb(),
                    'type'        => 'html'
                ),
            ),
            'wptelegram_search_wp' => array(
                array(
                    'name'     => 'which_post_type',
                    'label'    => __( 'Which post type(s) to search?', 'wptelegram' ),
                    'desc'     => '',
                    'type'     => 'multicheck',
                    'as_array' => true,
                    'default'  => 'post',
                    'options'  => $this->get_post_types(),
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                ),
                array(
                    'name'    => 'from_terms',
                    'label'   => __( 'Categories/Terms', 'wptelegram' ),
                    'type'    => 'select',
                    'class'   => 'no-fancy',
                    'default' => 'all',
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                    'options' => array(
                        'all'           => __( 'Search in all Categories/Terms', 'wptelegram' ),
                        'selected'      => __( 'Search only in selected ones', 'wptelegram' ),
                        'not_selected'  => __( 'Do not search in selected ones', 'wptelegram' ),
                    ),
                ),
                array(
                    'name'      => 'terms',
                    'label'     => '',
                    'desc'      => __( 'The rule will apply to the selected categories/terms and their children', 'wptelegram' ),
                    'type'      => 'select',
                    'multiple'  => true,
                    'grouped'   => true,
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                    'options'   => Wptelegram_Admin_Settings::get_all_terms(),
                ),
                array(
                    'name'    => 'from_authors',
                    'label'   => __( 'Authors', 'wptelegram' ),
                    'type'    => 'select',
                    'class'   => 'no-fancy',
                    'default' => 'all',
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                    'options' => array(
                        'all'           => __( 'Search from all Authors', 'wptelegram' ),
                        'selected'      => __( 'Search only from selected ones', 'wptelegram' ),
                        'not_selected'  => __( 'Do not search from selected ones', 'wptelegram' ),
                    ),
                ),
                array(
                    'name'      => 'authors',
                    'label'     => '',
                    'type'      => 'select',
                    'multiple'  => true,
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                    'options'   => Wptelegram_Admin_Settings::get_all_authors(),
                ),
            ),
            'wptelegram_search_msg' => array(
                array(
                    'name'              => 'start_reply',
                    'label'             => __( 'Reply to', 'wptelegram' ) . ' <code>/start</code> ' . __( 'command', 'wptelegram' ),
                    'desc'              => __( 'Structure of the message to be sent', 'wptelegram' ) . '.&nbsp;' . __( 'You can use', 'wptelegram' ) . '&nbsp;<code>{first_name}</code>',
                    'type'              => 'textarea',
                    'sanitize_callback' => 'wptelegram_sanitize_message_template',
                    'json_encoded'      => true,
                ),
                array(
                    'name'              => 'help_reply',
                    'label'             => __( 'Reply to', 'wptelegram' ) . ' <code>/help</code> ' . __( 'command', 'wptelegram' ),
                    'type'              => 'textarea',
                    'sanitize_callback' => 'wptelegram_sanitize_message_template',
                    'json_encoded'      => true,
                ),
                array(
                    'name'              => 'non_text_reply',
                    'label'             => __( 'Reply to non text messages', 'wptelegram' ),
                    'type'              => 'textarea',
                    'default'           => json_encode( __( 'Sorry, only text messages are supported.', 'wptelegram' ) ),
                    'sanitize_callback' => 'wptelegram_sanitize_message_template',
                    'json_encoded'      => true,
                ),
                array(
                    'name'    => 'text_reply',
                    'label'   => __( 'Reply to text messages with', 'wptelegram' ),
                    'type'    => 'select',
                    'class'   => 'no-fancy',
                    'desc'              => __( 'Other message types like photos, videos, voice notes etc. except inline search, will be ignored', 'wptelegram' ),
                    'default' => 'search_results',
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                    'options' => array(
                        'search_results'    => __( 'Search Results', 'wptelegram' ),
                        'recent_posts'      => __( 'Recent Posts', 'wptelegram' ),
                        'composed_message'  => __( 'Composed Message', 'wptelegram' ),
                    ),
                ),
                array(
                    'name'              => 'num_posts',
                    'label'             => __( 'Number of posts?', 'wptelegram' ),
                    'desc'              => __( 'Number of posts to be sent in reply', 'wptelegram' ),
                    'min'               => 1,
                    'max'               => 10,
                    'step'              => '1',
                    'type'              => 'number',
                    'default'           => 5,
                    'sanitize_callback' => 'intval'
                ),
                array(
                    'name'              => 'composed_message',
                    'label'             => __( 'Composed Message', 'wptelegram' ),
                    'type'              => 'textarea',
                    'sanitize_callback' => 'wptelegram_sanitize_message_template',
                    'json_encoded'      => true,
                ),
                array(
                    'name'              => 'result_template',
                    'label'             => __( 'Search Result Template', 'wptelegram' ),
                    'desc'              => __( 'Structure of the search result message to be sent', 'wptelegram' ),
                    'placeholder'       => __( 'e.g.', 'wptelegram' ) . "\n{title}\n{full_url}",
                    'type'              => 'textarea',
                    'sanitize_callback' => 'wptelegram_sanitize_message_template',
                    'json_encoded'      => true,
                    'emoji_container'   => true
                ),
                array(
                    'name'        => 'html',
                    'desc'        => $this->message_template_desc_cb(),
                    'type'        => 'html'
                ),
                array(
                    'name'              => 'cache_time',
                    'label'             => __( 'Cache Time', 'wptelegram' ),
                    'desc'              => __( 'The maximum amount of time in seconds that the result of the inline query may be cached on the Telegram server', 'wptelegram' ),
                    'placeholder'       => '300',
                    'min'               => 1,
                    'step'              => '1',
                    'type'              => 'number',
                    'default'           => 300,
                    'sanitize_callback' => 'wptelegram_sanitize_cache_time'
                ),
                array(
                    'name'              => 'thumb_url',
                    'label'             => __( 'Default Thumb URL', 'wptelegram' ),
                    'desc'              => __( 'URL of the default thumbnail for inline query results', 'wptelegram' ),
                    'type'              => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'button'            => array(
                        'name'  => __( 'Select' ),
                        'id'    => 'thumb-url-button',
                        'class' => 'button-primary',
                    ),
                ),
                array(
                    'name'        => 'thumb_html',
                    'desc'        => $this->thumb_html_cb(),
                    'type'        => 'html'
                ),
                array(
                    'name'    => 'excerpt_source',
                    'label'   => __( 'Excerpt Source', 'wptelegram' ),
                    'desc'    => '',
                    'type'    => 'radio',
                    'default' => 'post_content',
                    'sanitize_callback' => 'sanitize_text_field',
                    'options' => array(
                        'post_content'  => __( 'Post Content', 'wptelegram' ),
                        'before_more'  => __( 'Post Content before Read More tag', 'wptelegram' ),
                        'post_excerpt'  => __( 'Post Excerpt', 'wptelegram' ),
                    ),
                ),
                array(
                    'name'              => 'excerpt_length',
                    'label'             => __( 'Excerpt Length', 'wptelegram' ),
                    'desc'              => __( 'Number of words for the excerpt, to be taken from Post Content/Excerpt.', 'wptelegram' ),
                    'placeholder'       => '55',
                    'min'               => 1,
                    'max'               => 300,
                    'step'              => '1',
                    'type'              => 'number',
                    'default'           => 55,
                    'sanitize_callback' => 'wptelegram_sanitize_excerpt_length'
                ),
                array(
                    'name'    => 'parse_mode',
                    'label'   => __( 'Parse Mode', 'wptelegram' ),
                    'desc'    => '<a href="'. esc_url( 'https://core.telegram.org/bots/api/#formatting-options' ) . '" target="_blank">' . __( 'Learn more', 'wptelegram' ) . '</a>',
                    'type'    => 'radio',
                    'default' => 'none',
                    'sanitize_callback' => 'sanitize_text_field',
                    'options' => array(
                        'none'      => __( 'None', 'wptelegram' ),
                        'Markdown'  => __( 'Markdown style', 'wptelegram' ),
                        'HTML'      => __( 'HTML style', 'wptelegram' ),
                    ),
                ),
                array(
                    'name'    => 'misc',
                    'label'   => __( 'Link Preview and Notifications', 'wptelegram' ),
                    'desc'    => '',
                    'type'    => 'multicheck',
                    'options' => array(
                        'disable_web_page_preview' => __( 'Disable Web Page Preview', 'wptelegram' ) . ' (' . __( 'of the link in text', 'wptelegram' ) . ')',
                        'disable_notification'     => __( 'Disable Notifications', 'wptelegram' ),
                    ),
                    'sanitize_callback' => 'wptelegram_sanitize_array',
                ),
            ),
        );
        return apply_filters('wptelegram_search_admin_settings_fields_array', $settings_fields);
    }

	/**
	 * Render the text for the telegram section
	 *
	 * @since  1.0.0
	 */
	public function wptelegram_telegram_cb() {
		?>
		<div class="inside">
		<p><?php esc_html_e( 'In this section you can change the settings related to Telegram. ', 'wptelegram' );
		 esc_html_e( 'To let people search on your website using your bot, follow the steps below.', 'wptelegram' ); ?></p>
		 <p style="color:#f10e0e;text-align:center;"><b><?php echo __( 'ATTENTION!','wptelegram'); ?></b></p>
		 <ol style="list-style-type: decimal;">
		 	<li><?php echo __( 'Create a Bot (If you haven\'t), by sending','wptelegram' ) . ' <code>/newbot</code> ' . __( 'command to ','wptelegram' );
		 	echo ' <a href="https://t.me/BotFather"  target="_blank">@BotFather</a>';?></li>
		 	<li><?php esc_html_e( 'After completing the steps @BotFather will provide you the Bot Token.', 'wptelegram' );?></li>
		 	<li><?php esc_html_e( 'Copy the token and paste into the Bot Token field below. ', 'wptelegram' ); esc_html_e( 'For ease, use ', 'wptelegram' );?><a href="<?php echo esc_url( 'https://web.telegram.org' ); ?>" target="_blank">Telegram Web</a></li>
            <li><?php echo __( 'Hit', 'wptelegram' ) . ' <b>' . __( 'Save Changes', 'wptelegram' ) . '</b> ' . __( 'below', 'wptelegram' );?></li>
            <li><?php echo __( 'Hit', 'wptelegram' ) . ' <b>' . __( 'Set Webhook', 'wptelegram' ) . '</b> ' . __( 'below', 'wptelegram' );?>&nbsp;<span style="color:#f10e0e;">(<?php esc_html_e( 'Please make sure that webhook is set, otherwise the bot will not work', 'wptelegram' );?>)</span></li>
            <li><?php esc_html_e( 'To use the bot for inline search in a chat, enable inline queries status (If you haven\'t) for it.', 'wptelegram' );?>&nbsp;<?php echo esc_html__( 'You can do that by sending', 'wptelegram' ) . ' <code>/setinline</code> ' . esc_html__( 'command to @BotFather','wptelegram' );?></li>
            <li><?php esc_html_e( 'After sending the command, select your bot and then send some text for placeholder', 'wptelegram' );?></li>
		 	<li><?php esc_html_e( 'That\'s it. Happy WPTelagram :)', 'wptelegram' );?></li>
		 </ol>
		 </div>
		<?php
	}

    /**
     * get bot_token HTML
     *
     * @since  1.0.0
     * @return string
     */
    private function bot_token_html_cb( $section ) {
        $html = '<p style="margin-top:-30px;"><span id="wptelegram-'.$section.'-test" class="wptelegram-'.$section.'-desc description hidden">' . esc_html__( 'Test result: ', 'wptelegram' ) . '<b><span style="color:#bb0f3b;" id="bot-info"></span></b></span>
        <span id="wptelegram-'.$section.'-token-err" class="hidden"  style="color:#f10e0e;font-weight:bold;">' . esc_html__(' Invalid Bot Token', 'wptelegram' ) . '</span></p>';
        return $html;
    }

    /**
     * get webhook HTML
     *
     * @since  1.0.0
     * @return string
     */
    private function webhook_html_cb() {
        $is_settings_page = ( isset( $_GET['page'] ) && 'wptelegram_search' == $_GET['page'] );
        if ( ! $is_settings_page ) {
            return;
        }
        global $wptelegram_options;

        $bot_token = $wptelegram_options['search_tg']['bot_token'];
        $webhook_url = '';
        if ( '' == $bot_token ) {
            $notice = __( 'Webhook not set!', 'wptelegram' ) . '&nbsp;' . __( 'Please enter the bot token and save the settings to be able to set webhook.', 'wptelegram' );
            $level = 0;
        } elseif ( ! $this->is_set_webhook_url( $bot_token, $webhook_url ) ) {
            $notice = __( 'Webhook not set!', 'wptelegram' ) . '&nbsp;' . __( 'Please click the button below to set webhook.', 'wptelegram' );
            $level = 1;
        } else{
            $notice = __( 'Webhook is set!', 'wptelegram' );
            $level = 2;
        }
        if ( $level < 2 ) {
            $sign = '✕';
            $class = 'cross';
        } else{
            $sign = '✓';
            $class = 'tick';
        }
        $html = '<span class="wptelegram-sign ' . $class . '">' . $sign . '</span>&nbsp;' . '<span>' . $notice. '</span>';
        if ( 1 == $level ) {
            
            $html .= '<p><button id="wptelegram-search-webhook" type="button" class="button-secondary" href="' . esc_attr( 'https://api.telegram.org/bot' . $bot_token . '/setWebhook?url=' . urlencode_deep( $webhook_url ) . '&allowed_updates=' . $this->get_allowed_updates() ) . '" data-webhook-url="' . esc_attr( $webhook_url ) . '" onclick="setWebhook(this)">' . __( 'Set Webhook', 'wptelegram' ) . '</button></p><span id="wptelegram-search-webhook-info" style="color: #cc1212;"></span>';
        }
        return $html;
    }

    /**
     * get allowed update types
     *
     * @since  1.1.0
     * @return string
     */
    private function get_allowed_updates() {
        $allowed_updates = array(
            'message',
            'inline_query',
            'chosen_inline_result',
        );
        $allowed_updates = (array) apply_filters( 'wptelegram_search_allowed_updates', $allowed_updates );
        /**
         * json_encode does not work 
         * I don't know why
         * although it produces the same result
         * So, using implode
         */
        $allowed_updates = '["' . implode( '","', $allowed_updates ) . '"]';
        return $allowed_updates;
    }

    /**
     * get thumb HTML
     *
     * @since  1.0.0
     * @return string
     */
    private function thumb_html_cb() {
        global $wptelegram_options;
        $src = $wptelegram_options['search_msg']['thumb_url'];
        $atts = array(
            'id'    => 'wptelegram-search-thumb',
            'src'   => $src,
            'width' => '100'
        );
        if ( ! $src ) {
            $atts['class'] = 'hidden';
        }
        $html = '<img';
        foreach ( $atts as $att => $value ) {
            $html .= ' ' . $att . '="' . esc_attr( $value ) . '"';
        }
        $html .= '/>';
        return $html;
    }

    /**
     * checks if the webhook is set to current site
     *
     * @since  1.0.0
     *
     * @return boolean
     */
    private function is_set_webhook_url( $bot_token, &$webhook_url ) {
        $this->tg_api = new WPTelegram_Bot_API( $bot_token );

        $admin_post_url = admin_url( 'admin-post.php' );
        $args = array(
            'action'    => 'wptelegram_search',
            'bot_token' => $bot_token
        );

        /**
         * Add bot_token to the URL to make it secure
         * because only admin knows the bot token
         */
        $webhook_url = add_query_arg( $args, $admin_post_url );
        
        $webhook_info = $this->tg_api->getWebhookInfo();
        if ( ! is_wp_error( $webhook_info ) ) {
            $result = $webhook_info->get_result();
            if ( isset( $result['url'] ) && $webhook_url == $result['url'] ) {
                return true;
            }
        }
        return false;
    }

	/**
	 * get chat_ids HTML
	 *
	 * @since  1.2.0
	 * @return string
	 */
	private function chat_ids_html_cb( $section ) {
		$html = '<p style="margin-top:-30px;"><span id="wptelegram-'.$section.'-mem-count" class="hidden">' . esc_html__( "Members Count:", "wptelegram" ) . '</span></p>
		<ol id="wptelegram-'.$section.'-chat-list">
		</ol>
		<table id="wptelegram-'.$section.'-chat-table" class="hidden">
			<tbody>
				<tr>
					<th>' . esc_html__( "Chat_id", "wptelegram" ) . '</th>
					<th>' . esc_html__( "Name/Title", "wptelegram" ) . '</th>
					<th>' . esc_html__( "Chat Type", "wptelegram" ) . '</th>
					<th>' . esc_html__( "Test Status", "wptelegram" ) . '</th>
				</tr>
			</tbody>
		</table>';
		return $html;
	}

    /**
     * Render the text for the notify section
     *
     * @since  1.4.0
     */
    public function wptelegram_notify_cb() {
        ?>
        <div class="inside">
        <p><?php esc_html_e( 'In this section you can set/change the settings related to Notifications sent to Telegram', 'wptelegram' );?></p>
         <p style="color:#f10e0e;text-align:center;"><b><?php echo __( 'INSTRUCTIONS!','wptelegram'); ?></b></p>
        <p><b><?php esc_html_e( 'IMPORTANT! First complete at least first 4 steps in Telegram Settings section, otherwise notifications won\'t be sent', 'wptelegram' );?></b></p>
         <ul style="list-style-type: disc;margin-left: 20px;">
             <li><?php echo __( 'Every Telegram user or group has a unique ID called', 'wptelegram' );?> <i>chat_id</i>.</li>
             <li><?php echo __( 'It is different from a username and is visible only to bots.', 'wptelegram' );?></li>
             <li><?php echo __( 'This chat_id is used by the bots to send messages to a user or group.', 'wptelegram' );?></li>
             <li><?php echo __( 'In order to receive notifications through your bot, you need to find your', 'wptelegram' );?> <i>chat_id</i></li>
             <li><?php echo __( 'Follow these steps:', 'wptelegram' );?>
                 <ol style="list-style-type: decimal;">
                    <li><?php echo __( 'Send a message to ','wptelegram' );
                    echo '<a href="https://t.me/ChatIDBot"  target="_blank">@ChatIDBot</a>';?></li>
                    <li><?php esc_html_e( 'It will send you your Telegram chat_id.', 'wptelegram' );?></li>
                    <li><?php echo __( 'If you want to receive notifications into a group, then add @ChatIDBot to the group and it will send you the group ID.', 'wptelegram' );?>
                        <ul style="list-style-type: disc;margin-left: 20px;"">
                            <li><?php echo __( 'Don&apos;t forget to add your own bot to the group.', 'wptelegram' );?></li>
                        </ul>
                    <li><?php esc_html_e( 'Enter the received chat_id in the field below', 'wptelegram' );?></li>
                    <li><?php echo __( 'Start the conversation with your bot, bots can&apos;t initialize a conversation.', 'wptelegram' );?></li>
                 </ol>
             </li>
         </ul>
         </div>
        <?php
    }

    /**
     * get message template HTML
     *
     * @since  1.2.0
     * @return string
     */
    private function message_template_desc_cb() {
		$html = '<p style="margin-top:-15px;">' . esc_html__( 'You can use any text, emojis or these macros in any order: ', 'wptelegram' ) . '<b><i>(' . esc_html__( 'Click to insert', 'wptelegram' ) . ')</i></b>' . $this->get_macros() . '</p>
			<p>
				<span><strong>' . esc_html__( 'Note:', 'wptelegram' ) .'</strong></span>
				<ol>
					<li>' . esc_html__( 'Replace ', 'wptelegram' ) . '<code>taxonomy</code>' . esc_html__( 'in', 'wptelegram' ) .'<code>{[taxonomy]}</code> ' . esc_html__( 'with the name of the ', 'wptelegram' ) . '<a href="' . esc_url( 'https://codex.wordpress.org/Taxonomies' ) . '" target="_blank">' . esc_html__( 'taxonomy', 'wptelegram' ) . '</a>' . esc_html__( ' from which you want to ', 'wptelegram' ) . '<a href="' . esc_url( 'https://developer.wordpress.org/reference/functions/get_the_terms/' ) . '" target="_blank"> ' . esc_html__( 'get_the_terms', 'wptelegram' ) . '</a>, ' . esc_html__( 'attached to the post.', 'wptelegram' ) . esc_html__( 'For example ', 'wptelegram' ) . '<code>{[genre]}</code></li>
					<li>' . esc_html__( 'Replace', 'wptelegram' ) . ' <code>custom_field</code> ' . esc_html__( 'in', 'wptelegram' ) . ' <code>{[[custom_field]]}</code> ' . __( 'with the name', 'wptelegram' ) . ' (<code>meta_key</code>) of the ' . '<a href="' . esc_url( 'https://codex.wordpress.org/Custom_Fields' ) . ' target="_blank">' . esc_html__( 'Custom Field', 'wptelegram' ) . '</a>, ' . esc_html__( 'the value of which you want to add to the template. For example ', 'wptelegram' ) . '<code>{[[rtl_title]]}</code></li>
				</ol>
			</p>';
			return $html;
	}

    /**
     * get macros
     *
     * @since  1.3.0
     */
    private function get_macros() {
        $macros = array(
            '{ID}',
            '{title}',
            '{author}',
            '{excerpt}',
            '{content}',
            '{short_url}',
            '{full_url}',
            '{tags}',
            '{categories}',
            '{[taxonomy]}',
            '{[[custom_field]]}',
            );

        /**
         * If you add your own macros using this filter
         * You should use "wptelegram_macro_values" filter
         * to replace the macro with the corresponding values
         * See prepare_message() method in
         * wptelegram/includes/class-wptelegram-post-handler.php
         *
         */
        $macros = (array) apply_filters( 'wptelegram_settings_macros', $macros );

        $html = '';
        foreach ( $macros as $macro ) {
            $html .= '<button type="button" class="wptelegram-tag"><code>' . esc_html__( $macro ) . '</code></button>';
        }
        return $html;
    }

    /**
     * Set $this->post_types
     *
     * @since  1.2.0
     */
    public function set_post_types() {
		$this->post_types = get_post_types( array( 'public' => true ), 'objects' );
	}

	/**
	 * get registered post types
	 *
	 * @param  string $for the page or section to get_post_types for
	 *
	 * @since  1.2.0
	 * @return array
	 */
	public function get_post_types() {
		$arr = array();
		foreach ( $this->post_types  as $post_type ) {
			$arr[ $post_type->name ] = isset( $post_type->labels->singular_name ) ? $post_type->labels->singular_name . ' (' . $post_type->name . ')' : $post_type->name;
		}
		return $arr;
	}

	/**
	 * Render the meta box
	 *
	 * @since  1.0.0
	 */
	public function wptelegram_meta_box_cb() {
		global $post;
        global $wptelegram_options;
        
		wp_nonce_field( 'save_scheduled_post_meta', 'wptelegram_meta_box_nonce' );

		$chat_ids = $wptelegram_options['telegram']['chat_ids'];

	    $message_template = $wptelegram_options['message']['message_template'];

		if ( '' != $message_template ) {
			$message_template = json_decode( $message_template );
		}
		$message_template = apply_filters( 'wptelegram_message_template', $message_template, $post );
		?>
		<input type="checkbox" name="<?php echo esc_attr( 'wptelegram_override_switch'); ?>" id="<?php echo esc_attr( 'wptelegram_override_switch' ); ?>" value="on">
		<label for="<?php echo esc_attr( 'wptelegram_override_switch' ); ?>" id="<?php echo esc_attr( 'wptelegram_override_switch-label' ); ?>"></label><label for="<?php echo esc_attr( 'wptelegram_override_switch' ); ?>" id="switch-label"><?php esc_html_e( ' Override default settings', 'wptelegram' ); ?></label>
		<table class="form-table" id="wptelegram-meta-table">
			<tbody>
				<tr><th scope="row"><?php esc_html_e( 'Message', 'wptelegram' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="<?php echo esc_attr( 'wptelegram_send_message' ); ?>" id="<?php echo esc_attr( 'wptelegram_send_message' ); ?>" value="yes" checked><?php esc_html_e( 'Send', 'wptelegram' ); ?>
							</label>
							<br>
							<label>
								<input type="radio" name="<?php echo esc_attr( 'wptelegram_send_message' ); ?>" value="no"><?php esc_html_e( 'Do not Send', 'wptelegram' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr id="send_to"><th scope="row"><?php esc_html_e( 'Send to', 'wptelegram' ); ?></th>
					<td>
					<?php
					if ( $chat_ids ) : ?>
						<fieldset>
							<label>
								<input type="checkbox" id="<?php echo esc_attr( 'wptelegram_send_to_all' ); ?>" name="<?php echo esc_attr( 'wptelegram_send_to[all]' ); ?>" value="1" checked><?php esc_html_e( 'All Channels/Chats', 'wptelegram' ); ?>
							</label>
							<?php
							$chat_ids = explode( ',', $chat_ids );
							foreach ( $chat_ids as $chat_id ) :
								if ( '' == $chat_id ):
									continue;
								endif; ?>
								<br>
								<label class="<?php echo esc_attr( 'wptelegram_send_to' ); ?>" >
									<input type="checkbox" name="<?php echo esc_attr( 'wptelegram_send_to[]' ); ?>" value="<?php echo $chat_id; ?>"><?php echo $chat_id; ?>
								</label>
							<?php endforeach; ?>
						</fieldset>
					<?php else : ?>
						<span><?php esc_html_e( 'No Channels/Chat IDs found', 'wptelegram' ); ?></span>
					<?php endif; ?>
					</td>
				</tr>
				<tr id="message_template"><th scope="row"><?php esc_html_e( 'Template', 'wptelegram' ); ?></th>
					<td>
                        <div id="<?php echo esc_attr( 'wptelegram_message_template-container' ); ?>"></div>
						<textarea id="<?php echo esc_attr( 'wptelegram_message_template' ); ?>" name="<?php echo esc_attr( 'wptelegram_message_template' ); ?>" dir="auto"><?php echo esc_textarea( $message_template ); ?></textarea>
						<br><br>
						<?php
						echo call_user_func( array( $this, 'message_template_desc_cb' ) );
						?>
					</td>
				</tr>
			</tbody>
		</table>
	    <?php
	}

}
