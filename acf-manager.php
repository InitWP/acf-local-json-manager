<?php
/**
 * Set the folder where the acf json file wil be saved
 */
function NAMESPACE_set_acf_json_save_folder( $path ) {
    $path = dirname(__FILE__) . '/acf-json';
    return $path;
}
add_filter('acf/settings/save_json', 'NAMESPACE_set_acf_json_save_folder');

/**
 * Set the folder from where the acf json file wil be loaded
 */
function NAMESPACE_add_acf_json_load_folder( $paths ) {
    unset($paths[0]);
    $paths[] = dirname(__FILE__) . '/acf-json';
    return $paths;
}
add_filter('acf/settings/load_json', 'NAMESPACE_add_acf_json_load_folder');

/**
 * Import the advanced custom fields on admin init
 */
function NAMESPACE_sync_acf_fields() {
    $groups = acf_get_field_groups();
    $sync   = array();

    // return early if no field groups
    if( empty( $groups ) ) {
		return;
	}

    // find JSON field groups which have not yet been imported
    foreach( $groups as $group ) {
        $local      = acf_maybe_get( $group, 'local', false );
        $modified   = acf_maybe_get( $group, 'modified', 0 );
        $private    = acf_maybe_get( $group, 'private', false );

        // ignore DB / PHP / private field groups
        if( $local !== 'json' || $private ) {

            // do nothing

        } elseif( ! $group[ 'ID' ] ) {

            $sync[ $group[ 'key' ] ] = $group;

        } elseif( $modified && $modified > get_post_modified_time( 'U', true, $group[ 'ID' ], true ) ) {

            $sync[ $group[ 'key' ] ]  = $group;
        }
    }

    // return if no sync needed
    if( empty( $sync ) ) {
		return;
	}
    if( ! empty( $sync ) ) { //if( ! empty( $keys ) ) {
        $new_ids = array();

        foreach( $sync as $key => $v ) { //foreach( $keys as $key ) {

            // append fields
            if( acf_have_local_fields( $key ) ) {

                $sync[ $key ][ 'fields' ] = acf_get_local_fields( $key );

            }
            // import
            $field_group = acf_import_field_group( $sync[ $key ] );
        }
    }
}
add_action( 'admin_init', 'NAMESPACE_sync_acf_fields' );
