<?php

namespace PhilipNewcomer\PersistentTransients\Garbage_Collection;
use PhilipNewcomer\PersistentTransients as PersistentTransients;
use PhilipNewcomer\PersistentTransients\Helpers as Helpers;

/**
 * Schedules the garbage collection event, if it is not already scheduled.
 */
function maybe_schedule_event() {
	$hook = Helpers\get_garbage_collection_hook_name();

	if ( ! wp_next_scheduled( $hook ) ) {
		wp_schedule_event( time(), 'daily', $hook );
	}
}
add_action( 'init', __NAMESPACE__ . '\maybe_schedule_event' );

/**
 * Garbage collection function.
 */
function collect_garbage() {
    global $wpdb;

    $option_prefix = Helpers\get_option_prefix();
    $expiry_query = "{$option_prefix}%_expiration";
    $query         = $wpdb->prepare( "SELECT option_name from {$wpdb->options} WHERE `option_name` LIKE '%s' AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP()", $expiry_query );

    $option_names  = $wpdb->get_col( $query );

    if ( empty( $option_names ) ) {
        return 0;
    }

    foreach ( $option_names as $option_name ) {
        $transient_name = str_replace('_expiration', '', $option_name);
        delete_option( $transient_name );
        delete_option( $option_name );
    }
    return count($option_name);
}
add_action( Helpers\get_garbage_collection_hook_name(), __NAMESPACE__ . '\collect_garbage' );
