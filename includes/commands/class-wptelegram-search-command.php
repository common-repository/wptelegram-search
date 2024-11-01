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
 * Command Building functionality of the plugin.
 *
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
abstract class Wptelegram_Search_Command {

	/**
     * The name of the Telegram command.
     *
	 * @since	1.0.0
	 * @access	protected
     * @var string
     */
    protected $name;

    /**
     * Command Aliases
     * Helpful when you want to use same command with more than one name.
     *
	 * @since	1.0.0
	 * @access	protected
	 *
     * @var array
     */
    protected $aliases = array();

    /**
     *
	 * @since	1.0.0
	 * @access	protected
	 *
     * @var string The command description.
     */
    protected $description;

    /**
     *
	 * @since	1.0.0
	 * @access	protected
	 *
     * @var string Arguments passed to the command.
     */
    protected $arguments;

    /**
     * Command handler Object
     *
     * @since   1.0.0
     * @access  private
     *
     * @var Wptelegram_Search_Command_Handler $command_handler Command Handler
     */
    private $command_handler;

	/**
	 * The update object
	 *
	 * @since  	1.0.0
	 * @access 	private
	 * @var  	array 		$update
	 */
	private $update;

    /**
     * Magic Method to handle all ReplyWith Methods.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed|string
     */
    public function __call( $method, $arguments ) {
        $action = substr( $method, 0, 10 );
        if ( 'reply_with' === $action ) {
            $reply_name = ucfirst( strtolower( substr($method, 11 ) ) );
            $method_name = 'send' . $reply_name;
            
            $update = $this->get_update();
            $chat_id = $update['message']['chat']['id'];

            $params = array_merge( compact('chat_id'), $arguments[0] );
            $response = call_user_func(
                array(
                    $this->get_command_handler()->get_tg_api(),
                    $method_name
                ),
                $params
            );
            do_action( 'wptelegram_search_command_response', $response, $this );
            return $response;
        }
        return new WP_Error( 'not_command_instance', sprintf(
            "%s " . __( 'Method does not exist.', 'wptelegram' ),
            $method
        ) );
    }

    /**
     * Get Command Name.
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get Command Aliases
     *
     * @return array
     */
    public function get_aliases() {
        return $this->aliases;
    }

    /**
     * Set Command Name.
     *
     * @param $name
     *
     * @return Command
     */
    public function set_name( $name ) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Command Description.
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Set Command Description.
     *
     * @param $description
     *
     * @return Command
     */
    public function set_description( $description ) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get Arguments passed to the command.
     *
     * @return string
     */
    public function get_arguments() {
        return $this->arguments;
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
     * Returns an instance of Command Bus.
     *
     * @return Command_Bus
     */
    public function get_command_bus() {
        return $this->command_handler->get_command_bus();
    }

    /**
     * Returns an instance of Command Handler.
     *
     * @return Wptelegram_Search_Command_Handler
     */
    public function get_command_handler() {
        return $this->command_handler;
    }

    /**
     *
     */
    public function make( $command_handler, $arguments, $update )
    {
        $this->command_handler = $command_handler;
        $this->arguments = $arguments;
        $this->update = $update;

        return $this->handle( $arguments );
    }

    /**
     * Helper to Trigger other Commands.
     *
     * @param      $command
     * @param null $arguments
     *
     * @return mixed
     */
    protected function trigger_command( $command, $arguments = null )
    {
        return $this->command_handler->get_command_bus()->execute( $command, $arguments ?: $this->arguments, $this->update );
    }

    /**
     * {@inheritdoc}
     */
    abstract public function handle( $arguments );
}