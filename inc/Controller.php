<?php
/**
 
 */

namespace SHinse\ExportEMEventsToCSV\inc;

class Controller {
	private $admin;
	private $exporter;



	/**
	 * @param $plugin_file
	 */
	public function run( $plugin_file ) {
		$this->exporter = new Exporter();
		$this->admin = new Admin($this->exporter);


		//add plugin menu page
		add_action( 'admin_menu', array( $this->admin, 'register_plugin_page' ) );
		//we have to call the csv export before any other header is sent
		add_action( 'init', array( $this->admin, 'csv_export_listener' ) );
		//read csv delimiter from EM_Events Constant or set fallback value




	}

}