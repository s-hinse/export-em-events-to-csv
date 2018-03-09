<?php
/***********************
 *
 * Uninstall actions
 *
 *********************/

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
// We delete the delimiter option.
delete_option( 'export-em-events-to-csv-delimiter' );
