<?php
/**
 * Admin class for export-em-events-to-csv
 *
 * @author Sven Hinse
 * @package SHinse\ExportEMEventsToCSV\inc
 **/

namespace SHinse\ExportEMEventsToCSV\inc;

/**
 * Class Admin
 *
 * @package SHinse\ExportEMEventsToCSV\inc
 */
class Admin {

	/**
	 * Stores the Exporter object.
	 *
	 * @var Exporter
	 */
	private $exporter;

	/**
	 * Admin constructor.
	 *
	 * @param Exporter $exporter Stores the Exporter object.
	 */
	public function __construct( Exporter $exporter ) {

		$this->exporter = $exporter;

	}

	/**
	 * Registers admin page
	 */
	public function register_plugin_page() {

		// This sets the capability needed to access the menu.
		$cap = 'edit_others_events';

		add_submenu_page( 'edit.php?post_type=event', __( 'Export events as CSV', 'export-em-events-to-csv' ), __( 'Export Events as CSV', 'export-em-events-to-csv' ), $cap, 'export_em_events_to_csv', array(
			$this,
			'show_plugin_admin',
		) );
	}

	/**
	 * Shows the plugin admin page
	 */
	public function show_plugin_admin() {

		?>
		<div class="wrap">
			<p>
				<?php
				esc_html_e( 'Export a CSV file of all your event data by clicking the button.', 'export-em-events-to-csv' );
				// We try to get last used delimiter if available.
				$comma_checked     = '';
				$semicolon_checked = '';
				$delimiter         = get_option( 'export-em-events-to-csv-delimiter' );
				// If there is no stored delimiter we use ",".
				if ( false === $delimiter ) {
					$comma_checked = 'checked';
				} else {
					',' === $delimiter ? $comma_checked = '"checked' : $semicolon_checked = 'checked';
				}
				?>

			</p>
			<form action="" method="post">
				<p><strong><?php esc_html_e( 'Select delimiter:', 'export-em-events-to-csv' ); ?></strong></p>

				<input type="radio" name="delimiter" value=","<?php echo esc_html( $comma_checked ); ?>/>
				<label for="delimiter"><?php esc_html_e( 'Comma (,)', 'export-em-events-to-csv' ); ?></label>
				<p><input type="radio" name="delimiter" value=";"<?php echo esc_html( $semicolon_checked ); ?>/>
					<label for="delimiter"><?php esc_html_e( 'Semicolon (;)', 'export-em-events-to-csv' ); ?></label>
				</p>

				<?php $this->show_submit_button(); ?></form>
		</div>

		<?php
	}

	/**
	 * Displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'em_csv_export' );

		$html = '	<input type="hidden" name="action" value="csv_export" />';
		//phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo ( $html );
		submit_button( __( 'Export CSV file', 'export-em-events-to-csv' ) );

	}

	/**
	 * Checks if submit button was clicked
	 */
	public function csv_export_listener() {

		// check if export button was clicked.
		if ( isset ( $_POST['action'] ) && $_POST['action'] === "csv_export" && check_admin_referer( 'em_csv_export' ) ) {
			//check delimiter value
			if ( isset ( $_POST ['delimiter'] ) && ( ',' === $_POST ['delimiter'] || ';' === $_POST ['delimiter'] ) ) {
				$delimiter = $_POST ['delimiter'];
				update_option( 'export-em-events-to-csv-delimiter', $delimiter );
			} else {
				$delimiter = ',';
			}
			// Set delimiter and trigger csv file export.
			$this->exporter->set_delimiter( $delimiter );
			$this->exporter->deliver_csv_file();
		}
	}

}