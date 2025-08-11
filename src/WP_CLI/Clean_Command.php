<?php

/**
 * Commands for Persistent Transients.
 */
class Clean_Command extends WP_CLI_Command
{
    /**
     * Run the Persistent Transient Cleaner
     *
     * @param array $args Positional arguments.
     * @param array $assoc_args Keyed arguments.
     * @throws \WP_CLI\ExitException When an error occurs.
     *
     * @command clean
     */
    public function clean($args, $assoc_args)
    {
      \HiveIT\PersistentTransients\Garbage_Collection\collect_garbage();
    }

    /**
     * Get the command description.
     *
     * @return string Command description.
     */
    /**
     * Convert an exception into a WP CLI error.
     *
     * @param Exception $e The error object.
     */
    protected function print_error(Exception $e)
    {
        WP_CLI::error(
            sprintf(
            /* translators: %s refers to the exception error message */
                __('There was an error deleting an action: %s', 'action-scheduler'),
                $e->getMessage()
            )
        );
    }

    /**
     * Print a success message with the number of completed actions.
     *
     * @param int $actions_deleted Number of deleted actions.
     */
    protected function print_success(int $actions_deleted)
    {
        WP_CLI::success(
            sprintf(
            /* translators: %d refers to the total number of actions deleted */
                _n('%d action deleted.', '%d actions deleted.', $actions_deleted, 'action-scheduler'),
                $actions_deleted
            )
        );
    }
}
