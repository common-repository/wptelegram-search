<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://t.me/ManzoorWaniJK
 * @since      1.0.0
 *
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wptelegram_Search
 * @subpackage Wptelegram_Search/includes
 * @author     Manzoor Wani <manzoorwani.jk@gmail.com>
 */
class Wptelegram_Search {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wptelegram_Search_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Title of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $title    Title of the plugin
	 */
	protected $title;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Status of the Core plugin
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      bool    $core_active    Core plugin status
	 */
	protected $core_active = false;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->title = __( 'WP Telegram Search', 'wptelegram' );
		$this->plugin_name = 'wptelegram_search';
		$this->version = WPTELEGRAM_SEARCH_VER;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wptelegram_Search_Loader. Orchestrates the hooks of the plugin.
	 * - Wptelegram_Search_i18n. Defines internationalization functionality.
	 * - Wptelegram_Search_Admin. Defines all hooks for the admin area.
	 * - Wptelegram_Search_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/class-wptelegram-search-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/class-wptelegram-search-i18n.php';

		/**
		 * The miscellaneous functions
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/wptelegram-search-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/admin/class-wptelegram-search-admin.php';

		/**
		 * The class responsible for rendering all the settings in the admin area
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/admin/class-wptelegram-search-admin-settings.php';

		/**
		 * The class responsible for handling the Telegram webhook updates
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/handlers/class-wptelegram-search-update-handler.php';

		/**
		 * The class responsible for handling the Message object of an update
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/handlers/class-wptelegram-search-message-handler.php';

		/**
		 * The class responsible for handling the text command
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/handlers/class-wptelegram-search-command-handler.php';

		/**
		 * The class responsible for handling the normal text messages
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/handlers/class-wptelegram-search-text-handler.php';

		/**
		 * The class responsible for handling inline_query
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/handlers/class-wptelegram-search-inline-handler.php';

		/**
		 * The class responsible for handling the post query
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/handlers/class-wptelegram-search-query-handler.php';

		/**
		 * The parent class for all commands
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/commands/class-wptelegram-search-command.php';

		/**
		 * The class responsible for handling command list and their properties
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/commands/class-wptelegram-search-command-bus.php';

		/**
		 * The class responsible for handling /start command
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/commands/class-wptelegram-search-start-command.php';

		/**
		 * The class responsible for handling /help command
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/commands/class-wptelegram-search-help-command.php';

		/**
		 * The class responsible for handling /search command
		 * 
		 */
		require_once WPTELEGRAM_SEARCH_DIR . '/includes/commands/class-wptelegram-search-search-command.php';

		$this->loader = new Wptelegram_Search_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wptelegram_Search_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wptelegram_Search_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wptelegram_Search_Admin( $this->get_plugin_title(), $this->get_plugin_name(), $this->get_version() );

		if ( ! self::is_core_active() ) {
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notice_for_core' );
			return;
		}

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_sub_menu', 12 );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		// to be used for webhook
		$this->loader->add_action( 'admin_post_nopriv_wptelegram_search', $plugin_admin, 'wptelegram_search_handle_webhook' );
		$this->loader->add_action( 'admin_post_wptelegram_search', $plugin_admin, 'wptelegram_search_handle_webhook' );
	}

    /**
     * Check if the Core Plugin (WP Telegram) is installed and active
     *
     * @since  1.0.0
     */
    public static function is_core_active() {

        $plugin = 'wptelegram/wptelegram.php';

        if ( in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) ) {
        	return true;
        }
        if ( is_multisite() ){
	        $plugins = get_site_option( 'active_sitewide_plugins' );
		    if ( isset( $plugins[ $plugin ] ) ){
		        return true;
		    }
        }
	    return false;
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The title of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The title of the plugin.
	 */
	public function get_plugin_title() {
		return $this->title;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wptelegram_Search_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
