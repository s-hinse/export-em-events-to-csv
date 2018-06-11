<?php
/**
 */

namespace SHinse\ExportEMEventsToCSV\inc;

/**
 * Class Controller
 *
 * @package SHinse\ExportEMEventsToCSV\inc
 */
class Controller {

	/** The Admin object.
	 *
	 * @var Admin
	 */
	private $admin;

	/** The Exporter object.
	 *
	 * @var Exporter
	 */
	private $exporter;

	/**
	 * Registers plugin activation and inits hooks.
	 *
	 * @param  String $em_to_csv_file Path to main plugin file.
	 */
	public function run( $em_to_csv_file ) {

		register_activation_hook( $em_to_csv_file, array( $this, 'activate' ) );



		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Callback function for activation hook.
	 */
	public function activate() {

		if ( ! is_plugin_active( 'events-manager/events-manager.php' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die(wp_kses_post(
				'<p>' .

				__( 'This plugin cannot be activated because it requires the Events Manager plugin to be activated first. ', 'export-em-events-to-csv' )

				. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'back', 'export-em-events-to-csv' ) . '</a>'
			));

		}

	}

	/**
	 * Plugin init function.
	 */
	public function init() {

		// This sets the capability needed to run the plugin.
		$cap = 'edit_others_events';

		if ( current_user_can( $cap ) ) {
			// Set up autoloader.
			require_once 'Autoloader.php';
			Autoloader::register();

			$this->exporter = new Exporter();
			$this->admin    = new Admin( $this->exporter );

			// Add plugin menu page.
			add_action( 'admin_menu', array( $this->admin, 'register_plugin_page' ) );
			// We have to call the csv export before any other header is sent.
			add_action( 'init', array( $this->admin, 'csv_export_listener' ) );

		}

	}
}
