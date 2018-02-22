<?php
/***********************
 * Uninstall actions
 */
if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}
check_admin_referer( 'bulk-plugins' );

if ( __FILE__ != WP_UNINSTALL_PLUGIN ) {
	return;
}
//delete the delimiter option
delete_option('export-em-events-to-csv-delimiter');