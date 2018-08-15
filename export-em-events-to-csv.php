<?php /**
 * Plugin Name:   Export events to CSV (Addon for Events Manager)
 * Plugin URI:    ${Plugin_Uri}
 * Description:  Exports all Events and their locations into a CSV file.
 * Author:       Sven Hinse
 * Author URI:    http://www.svenhinse.de
 * Contributors:  s-hinse
 * Version:       1.2.1
 * Text Domain:   export-em-events-to-csv
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 **/

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if ( ! is_admin() ) {
	return;
}
em_to_csv_load_textdomain();


if ( ! check_php_version() ) {
	add_action( 'admin_notices', 'em_to_csv_admin_notice_wrong_php_version' );

	return;

}



// Load the plugin main file and start the plugin.
require_once 'inc/Controller.php';
$em_to_csv_file   = __FILE__;
$em_to_csv_plugin = new \SHinse\ExportEMEventsToCSV\inc\Controller();
$em_to_csv_plugin->run( $em_to_csv_file );

/**
 * Registers the textdomain.
 */
function em_to_csv_load_textdomain() {

	$lang_dir = plugin_basename( __DIR__ ) . '/languages/';
	load_plugin_textdomain( 'export-em-events-to-csv', false, $lang_dir );
}


/**
 * Checks if the minimum required PHP version is  installed.
 *
 * @return bool
 */
function check_php_version() {
	$correct_php_version = version_compare( phpversion(), '5.6.0', '>=' );

	return $correct_php_version;
}

/**
 * Shows an admin notice if the PHP version is too old.
 */
function em_to_csv_admin_notice_wrong_php_version() {
	// translators: %1s  is the minimum required PHP version.
	$error_message = sprintf( __( 'The plugin export-em-events-to-csv cannot be activated because it requires at least PHP version %1$s. ', 'export-em-events-to-csv' ),
	5.6 );

	$error_message .= __( 'You are running PHP ', 'export-em-events-to-csv' ) . esc_html( phpversion() );
	echo wp_kses_post( '<div class="notice error em-events-to-csv-notice is-dismissible" ><p>' . $error_message . '</p></div>' );


}

