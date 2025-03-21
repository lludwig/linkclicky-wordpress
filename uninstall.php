<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// remove these options since we are uninstalled
unregister_setting('linkclicky', 'linkclicky-domain-name');
unregister_setting('linkclicky', 'linkclicky-rvmedia');
unregister_setting('linkclicky', 'linkclicky-gobankingrates');
unregister_setting('linkclicky', 'linkclicky-api-server');
unregister_setting('linkclicky', 'linkclicky-api-key');
// delete the options from the database
delete_option('linkclicky-domain-name');
delete_option('linkclicky-rvmedia');
delete_option('linkclicky-gobankingrates');
delete_option('linkclicky-api-server');
delete_option('linkclicky-api-key');
