<?php
/**
 * Created by PhpStorm.
 * User: SvenH
 * Date: 14.08.2016
 * Time: 10:02
 */

namespace SHinse\ExportEMEventsToCSV\inc;

class Admin {

	private  $exporter ;

	public function __construct( Exporter $exporter ) {

		$this->exporter = $exporter;

	}

	/**
	 *registers admin page
	 */
	public function register_plugin_page() {

		//this sets the capability needed to access the menu

		$cap = 'edit_others_events';

		add_submenu_page( 'edit.php?post_type=event', __( 'Export events as CSV', 'export-em-events-to-csv' ),
		                  __( 'Export Events as CSV', 'export-em-events-to-csv' ), $cap, 'export_em_events_to_csv',
		                  array( $this, 'show_plugin_admin' ) );
	}

	/**
	 *shows the plugin admin page
	 */
	public function show_plugin_admin() {

		?>
		<div class="wrap"><p><?php esc_html_e( 'Export a CSV file of all your event data by clicking the button.',
		                                       'export-em-events-to-csv' ); ?> </p>
			<form action="" method="post">
				<p><strong><?php esc_html_e( 'Select delimiter:', 'export_em_events_to_csv' ); ?></strong></p>

				<input type="radio" name="delimiter" value=",">
				<label for="delimiter"><?php esc_html_e( 'comma (,)', 'export-em-events-to-csv' ) ?></label>
				<p><input type="radio" name="delimiter" value=";" checked />
					<label for="delimiter"><?php esc_html_e( 'colon (;)', 'export-em-events-to-csv' ) ?></label></p>

				<?php $this->show_submit_button(); ?></form>
		</div>

		<?php
	}

	/**
	 *displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'em_csv_export' );

		$html = '	<input type="hidden" name="action" value="csv_export" />';
		echo $html;
		submit_button( __( 'Export CSV file', 'export-em-events-to-csv' ) );

	}

	/**
	 * checks if submit button was clicked
	 */
	public function csv_export_listener() {

		//check if export button was clicked
		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "csv_export" && check_admin_referer( 'em_csv_export' ) ) {

			if ( isset ( $_POST [ 'delimiter' ] ) ) {
				$delimiter = $_POST [ 'delimiter' ];
			}
			$this->exporter->set_delimiter( $delimiter );
			$this->exporter->deliver_csv_file();
		}
	}

}