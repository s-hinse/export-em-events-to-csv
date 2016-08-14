<?php # -*- coding: utf-8 -*-

namespace SHinse\ExportEMEventsToCSV\inc;

use SHinse\ExportEMEventsToCSV\inc\sub\subclass;

register_activation_hook( __FILE__, __NAMESPACE__ . '\em_to_csv_activate' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

function em_to_csv_activate() {

	if ( ! is_plugin_active( 'events-manager/events-manager.php' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(
			'<p>' .
			sprintf(
				__( 'This plugin can not be activated because it requires the Events Manager plugin to be activated first. ', 'export-em-to-csv' )

			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'back', 'export-em-events-to-csv' ) . '</a>'
		);

	}

}

function init() {

	//this sets the capability needed to run the plugin

	$cap = 'edit_others_events';

	if ( current_user_can( $cap ) ) {
		//set up autoload
		require_once ('Autoloader.php');
		Autoloader::register();



		//start the plugin
		$plugin = new Controller();
		$plugin->run( __FILE__ );
		


	}

}