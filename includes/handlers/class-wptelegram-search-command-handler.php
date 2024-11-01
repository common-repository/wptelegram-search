<?php

/**
 * Command Handling functionality of the plugin.
 *
 * @link       https://t.me/WPTelegram
 * @since      1.0.0
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes/handlers
 */

/**
 * Command Handling functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Command_Handler {

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
	 * Bot commands
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array	$commands
	 */
	private $commands;

    /**
	 * Bot commands
	 *
	 * @since  	1.0.0
	 * @access 	private
	 *
     * @var Command_Bus|null Command Bus.
     */
    private $command_bus;

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

		$this->command_bus = new Wptelegram_Search_Command_Bus( $this );

		$this->setup_command_system();
	}

    /**
     * Returns Command Bus.
     *
     * @return Command_Bus
     */
    public function get_command_bus() {
        return $this->command_bus;
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
	 * Set up the basics
	 *
	 * @since  1.0.0
	 *
	 */
	private function setup_command_system() {
		$classes = array(
			'Wptelegram_Search_Start_Command',
			'Wptelegram_Search_Help_Command',
			'Wptelegram_Search_Search_Command',
		);
 
        /**
         * Filter command classes
         *
         * Every command class should extend the
         * Wptelegram_Search_Command class
         *
         * $name and $description properties of every class
         * should be added with the appropriate values,
         * which will be used when processing the command.
         *
         * Every command class should implement the handle($args) method
         * which would be called when a user sends the command
         *
         * @since	1.0.0
         *
         * @param	array	$classes	An array of all the command classes
         */
		$this->commands = (array) apply_filters( 'wptelegram_search_command_classes', $classes );
		$this->command_bus->add_commands( $this->commands );
	}

    /**
     * Process the command
     *
     * @since	1.0.0
     *
     */
    public function process_command() {
        $this->get_command_bus()->handler( $this->text, $this->update );
    }
}