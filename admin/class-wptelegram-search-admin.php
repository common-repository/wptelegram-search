<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://t.me/ManzoorWaniJK
 * @since      1.0.0
 *
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/admin
 * @author     Manzoor Wani <manzoorwani.jk@gmail.com>
 */
class Wptelegram_Search_Admin {

	/**
	 * Title of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $title    Title of the plugin
	 */
	protected $title;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * WPTelegram_Settings_API
     *
     * @var Object
     */
    private $settings_api;

	/**
	 * Object which handles settings sections, fields and metabox
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var Wptelegram_Search_Admin_Settings $settings Admin Settings Object
	 */
	private $settings;

	/**
	 * The status of the core plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $core_status	The status of the core plugin.
	 */
	private $core_status;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $title, $plugin_name, $version ) {

		$this->title = $title;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings = new Wptelegram_Search_Admin_Settings();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wptelegram-search-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wptelegram-search-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Initialize the admin area of the plugin
     *
     * @since  1.0.0
     */
    public function admin_init() {
    	if ( ! version_compare( WPTELEGRAM_VER, '1.5.2', '>=' ) ) {
    		$this->core_status = 'out_dated';
    		return;
    	}

		$this->settings_api = new WPTelegram_Settings_API( $this->plugin_name );

        /**
		 * use admin_init hook to set post types
		 * because add_meta_boxes hook would not be able to
		 * return Custom Post Types
		 */
		$this->settings->set_post_types();
        //set the settings
        $this->settings_api->set_sections( $this->settings->get_settings_sections() );
        $this->settings_api->set_fields( $this->settings->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

	/**
	 * Add an entry in the Admin Menu
	 *
	 * @since  1.0.0
	 */
	public function add_sub_menu() {

		add_submenu_page(
	        'wptelegram',
			__( 'WPTelegram Search', 'wptelegram' ),
			__( 'Search', 'wptelegram' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_settings_page' )
        );
	}

	/**
	 * Show admin notice for Core requirement
	 *
	 * @since  1.0.0
	 */
	public function admin_notice_for_core( $status = 'inactive' ) {
		$url = 'https://wordpress.org/plugins/wptelegram';
		
		if ( current_user_can( 'activate_plugins' ) ) {
			$url = network_admin_url( 'plugin-install.php?s=wptelegram&tab=search&type=term&plugin-search-input=Search+Plugins' );
		}
		$message = '<b>' . $this->title . '</b>&nbsp;' . __( 'requires the latest version of the core plugin', 'wptelegram' ) . '&nbsp;<b><a href="' . esc_url( $url ) . '" target="_blank">' . __( 'WP Telegram', 'wptelegram' ) . '</a></b>&nbsp;' . __( 'installed and active.', 'wptelegram' );
		if ( 'out_dated' == $status ) {
			$message .= '&nbsp;' . __( 'Please update to the latest version', 'wptelegram' );
		}
		?>
		<div class="notice notice-error">
		  <p><?php echo $message; ?></p>
		</div>
		<?php
	}

	/**
	 * Render the menu page for plugin
	 *
	 * @since  1.0.0
	 */
	public function display_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.' ) );
		}
		if ( 'out_dated' == $this->core_status ) {
			$this->admin_notice_for_core( 'out_dated' );
    		return;
    	}
        $this->load_select2();
        $this->load_emojioneArea();
		echo '<div class="wrap wptelegram" id="wptelegram-wrap">';

        include_once WPTELEGRAM_DIR . '/admin/partials/wptelegram-admin-header.php';

        add_action( 'wptelegram_before_submit_button', array( $this, 'display_remove_settings' ) );

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo '</div>';
        // the template containing js
        include_once 'partials/wptelegram-search-admin-display.php';
	}

	/**
	 * Render the remove settings checkbox
	 *
	 * @since  1.0.0
	 */
	public function display_remove_settings() {
		global $wptelegram_options;
		$remove_settings = isset( $wptelegram_options['search_wp']['remove_settings'] ) ? $wptelegram_options['search_wp']['remove_settings'] : 'on';
		$float = is_rtl() ? 'left' : 'right';
		?>
		<div class="wptelegram-before-submit" style="float:<?php echo $float; ?>">
			<input type="hidden" name="wptelegram_search_wp[remove_settings]" value="off">
			<label for="wptelegram_search_wp[remove_settings]"><input type="checkbox" class="checkbox remove_settings" id="wptelegram_search_wp[remove_settings]" name="wptelegram_search_wp[remove_settings]" value="on" <?php checked( $remove_settings, 'on' ); ?>><?php esc_html_e( ' Remove settings on uninstall', 'wptelegram' ); ?></label>
		</div>
		<?php
	}

	/**
	 * Load Emoji One Area
	 *
	 * @since  1.3.0
	 */
	private function load_emojioneArea(){
		wp_enqueue_style( $this->plugin_name.'-emojicss', WPTELEGRAM_URL . '/admin/emoji/emojionearea.min.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->plugin_name.'-emojijs', WPTELEGRAM_URL . '/admin/emoji/emojionearea.min.js', array(), $this->version, 'all' );
	}

	/**
	 * Load Select2
	 *
	 * @since  1.3.8
	 */
	private function load_select2(){
		wp_enqueue_style( $this->plugin_name.'-select2css', WPTELEGRAM_URL . '/admin/select2/select2.min.css', array(), $this->version, 'all' );
        wp_enqueue_script( $this->plugin_name.'-select2js', WPTELEGRAM_URL . '/admin/select2/select2.min.js', array(), $this->version, 'all' );
	}

	/**
	 * Handle Webhook payload
	 *
	 * @since    1.0.0
	 */
	public function wptelegram_search_handle_webhook() {
		global $wptelegram_options;
	    
		if ( ! isset( $_GET['bot_token'] ) ) {
			return;
		}
		$bot_token = $wptelegram_options['search_tg']['bot_token'];
		// verify bot token
		if ( ! $bot_token || $bot_token != $_GET['bot_token'] ) {
			return;
		}
		
		$json = file_get_contents( 'php://input' );
		$update = json_decode( $json, true );
		// if is not a valid update
		if ( NULL === $update || ! isset( $update['update_id'] ) || ! is_int( $update['update_id'] ) ) {
			return;
		}
		// Pass the update to the handler
		$this->wptelegram_search_handle_update( $update, $bot_token );
	}

	/**
	 * Handle Webhook payload
	 *
	 * @since    1.0.0
	 */
	public function wptelegram_search_test() {
		global $wptelegram_options;
	    
		
		$bot_token = $wptelegram_options['search_tg']['bot_token'];
		
		
		$json = '{"update_id":894414577,"message":{"message_id":10799,"from":{"id":65220754,"first_name":"Manzoor","last_name":"Wani","username":"ManzoorWaniJK"},"chat":{"id":65220754,"first_name":"Manzoor","last_name":"Wani","username":"ManzoorWaniJK","type":"private"},"date":1487443892,"text":"/help nothing"}}';
		$update = json_decode( $json, true );
		// if is not a valid update
		if ( NULL === $update || ! isset( $update['update_id'] ) || ! is_int( $update['update_id'] ) ) {
			return;
		}
		// Pass the update to the handler
		$this->wptelegram_search_handle_update( $update, $bot_token );
	}

	/**
	 * Handle update
	 *
	 * @param	array	$update	An update from Telegram
	 * @param	string	$bot_token Telegram Bot Token
	 * @since    1.0.0
	 */
	private function wptelegram_search_handle_update( $update, $bot_token ) {
		// Initialize the update handler class
		$handle = new Wptelegram_Search_Update_Handler( $bot_token );
		
		$transient = 'wptelegram_search_processing_update_id_' . $update['update_id'];
		/**
		 * Check if the update is locked for processing
		 * to avoid duplicates
		 */
		if ( get_site_transient( $transient ) ) {
			return;
		}

		$handle->set_update( $update );
		/**
		 * Lock the update for processing
		 * Assuming that 60 seconds (by default) are enough
		 * to process an update
		 */
		$expiration = (int) apply_filters( 'wptelegram_search_update_processing_duration', 60 );
		
		set_site_transient( $transient, true, $expiration );
		// process the update
		$res = $handle->process();

		// unlock the update if processed before expiration
		delete_site_transient( $transient );
	}

}
