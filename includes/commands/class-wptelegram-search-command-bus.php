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
 * inspired from https://github.com/irazasyed/telegram-bot-sdk
 *
 * @package    Wptelegram
 * @subpackage Wptelegram/includes
 * @author     Manzoor Wani <@manzoorwanijk>
 */
class Wptelegram_Search_Command_Bus {

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
     * @var Command[] Holds all commands.
     */
    protected $commands = array();

    /**
     * @var Command[] Holds all commands' aliases.
     */
    protected $command_aliases = array();

    /**
     * Instantiate Command Bus.
     *
     * @param Api $telegram
     *
     * @throws TelegramSDKException
     */
    public function __construct( $handler ) {
        $this->command_handler = $handler;
    }

    /**
     * Returns the list of commands.
     *
     * @return array
     */
    public function get_commands() {
        return $this->commands;
    }

    /**
     * Returns the command_handler
     *
     * @return Wptelegram_Search_Command_Handler
     */
    public function get_command_handler() {
        return $this->command_handler;
    }

    /**
     * Add a list of commands.
     *
     * @param array $commands
     *
     * @return CommandBus
     */
    public function add_commands( array $commands ) {
        foreach ( $commands as $command ) {
            $this->add_command( $command );
        }

        return $this;
    }

    /**
     * Add a command to the commands list.
     *
     * @param CommandInterface|string $command Either an object or full path to the command class.
     *
     * @throws TelegramSDKException
     *
     * @return CommandBus
     */
    public function add_command( $command ) {
        if ( ! is_object( $command ) ) {
            if ( ! class_exists( $command ) ) {
                return new WP_Error( 'command_class_not_found', sprintf(
                        "%s " . __( 'class not found! Please make sure the class exists.', 'wptelegram' ),
                        $command
                    ) );
            }

            $command = new $command();
        }

        if ( $command instanceof Wptelegram_Search_Command ) {

            $this->commands[ $command->get_name() ] = $command;

            $aliases = $command->get_aliases();

            if ( empty( $aliases ) ) {
                return $this;
            }

            foreach ( $command->get_aliases() as $alias ) {
                if ( isset( $this->commands[ $alias ] ) ) {
                    return new WP_Error( 'alias_conflict', sprintf(
                        "%s " . __( 'alias conflicts with command name of', 'wptelegram' ) . " %s " . __( 'try with another name or remove this alias from the list.', 'wptelegram' ),
                        
                        $alias,
                        get_class( $command )
                    ) );
                }

                if ( isset( $this->command_aliases[ $alias ] ) ) {
                    return new WP_Error( 'alias_conflict', sprintf(
                        "%s " . __( 'alias conflicts with another command\'s alias list', 'wptelegram' ) . " %s " . __( 'try with another name or remove this alias from the list.', 'wptelegram' ),
                        $alias,
                        get_class( $command )
                    ) );
                }

                $this->command_aliases[ $alias ] = $command;
            }

            return $this;
        }
        return new WP_Error( 'not_command_instance', sprintf(
            "%s " . __( 'Command class should be an instance of "Wptelegram_Search_Command"', 'wptelegram' ),
            get_class( $command )
        ) );
    }

    /**
     * Remove a command from the list.
     *
     * @param $name
     *
     * @return CommandBus
     */
    public function remove_command( $name ) {
        unset( $this->commands[ $name ] );

        return $this;
    }

    /**
     * Removes a list of commands.
     *
     * @param array $names
     *
     * @return CommandBus
     */
    public function remove_commands( array $names ) {
        foreach ( $names as $name ) {
            $this->remove_command( $name );
        }

        return $this;
    }

    /**
     * Parse a Command for a Match.
     *
     * @param $text
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function parse_command( $text ) {
        if ( trim( $text ) === '' ) {
            return new WP_Error( 'empty_message', __( 'Message is empty, Cannot parse for command', 'wptelegram' )
            );
        }

        preg_match( '/^\/([^\s@]+)@?(\S+)?\s?(.*)$/s', $text, $matches );

        return $matches;
    }

    /**
     * Handles Inbound Messages and Executes Appropriate Command.
     *
     * @param $text
     * @param $update
     *
     * @return Update
     */
    public function handler( $text, $update ) {
        $match = $this->parse_command( $text );
        if ( ! empty( $match ) ) {
            $command = strtolower( $match[1] ); //All commands must be lowercase.
            $arguments = $match[3];

            $this->execute( $command, $arguments, $update );
        }

        return $update;
    }

    /**
     * Execute the command.
     *
     * @param $name
     * @param $arguments
     * @param $update
     *
     * @return mixed
     */
    protected function execute( $name, $arguments, $update )
    {
        if ( array_key_exists( $name, $this->commands ) ) {
            return $this->commands[ $name ]->make( $this->command_handler, $arguments, $update );
        } elseif ( array_key_exists( $name, $this->command_aliases ) ) {
            return $this->command_aliases[ $name ]->make( $this->command_handler, $arguments, $update );
        } /*elseif ( array_key_exists( 'help', $this->commands ) ) {
            return $this->commands['help']->make( $this->command_handler, $arguments, $update );
        }*/
        else{
            $text = __( 'Unknown command', 'wptelegram' );
            $update = $this->get_command_handler()->get_update();
            $chat_id = $update['message']['chat']['id'];
            $params = apply_filters(
                'wptelegram_search_unknown_command_params',
                compact( 'text', 'chat_id' ),
                $this->get_command_handler()
            );
            $this->get_command_handler()->get_tg_api()->sendMessage( $params );
        }
    }
}
