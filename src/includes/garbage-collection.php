<?php

namespace HiveIT\PersistentTransients\Garbage_Collection;
use HiveIT\PersistentTransients as PersistentTransients;
use HiveIT\PersistentTransients\Helpers as Helpers;

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
 * Select all persistent transient options and delete those that have expired.
 * Using the SQL query to select options that match the persistent transient prefix and have an expiration timestamp less than the current time is much faster than the previous approach.
 */
function collect_garbage() {
	global $wpdb;

	$option_prefix = Helpers\get_option_prefix();
    $expiry_query = "{$option_prefix}%_expiration";
	$query         = $wpdb->prepare( "SELECT option_name from {$wpdb->options} WHERE `option_name` LIKE '%s' AND CAST(option_value AS UNSIGNED) < UNIX_TIMESTAMP()", $expiry_query );

	$option_names  = $wpdb->get_col( $query );

	if ( empty( $option_names ) ) {
		return;
	}

	foreach ( $option_names as $option_name ) {
        $transient_name = str_replace($option_name, '', '_expiration');
        PersistentTransients\delete( $transient_name );
        PersistentTransients\delete( $option_name );
	}
}
add_action( Helpers\get_garbage_collection_hook_name(), __NAMESPACE__ . '\collect_garbage' );
