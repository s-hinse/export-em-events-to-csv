<?php
/**
 * Exporter class for export-em-events-to-csv
 *
 * @author: s-hinse
 * @package export-em-events-to-csv
 */

namespace SHinse\ExportEMEventsToCSV\inc;

/**
 * Class Exporter
 *
 * @package SHinse\ExportEMEventsToCSV\inc
 */
class Exporter {
	//phpcs:disable WordPress.VIP.DirectDatabaseQuery.DirectQuery

	/** Delimiter storage.
	 *
	 * @var string The delimiter to use in the csv file
	 */

	private $delimiter;

	/**
	 * Returns the current delimiter.
	 *
	 * @return string The delimiter for the csv file
	 */
	public function get_delimiter() {

		return $this->delimiter;
	}

	/** Sets the delimiter.
	 *
	 * @param string $delimiter The desired delimiter.
	 */
	public function set_delimiter( $delimiter ) {

		$this->delimiter = $delimiter;
	}

	/**
	 * Reads all events from the database
	 *
	 * @return array  Array with events
	 */
	private function read_events_from_db() {

		global $wpdb;
		$prefix = $wpdb->prefix;
		$table  = $prefix . 'em_events';
		$events = $wpdb->get_results( 'SELECT * FROM ' . $table, ARRAY_A );

		return $events;

	}

	/**
	 * Returns an array with the the location details for the given location id
	 *
	 * @param int $location_id The id of the location for the location details query.
	 *
	 * @return mixed  Associative array with the db row of the location with $location_id,
	 *                False, if location with this ID is not set.
	 */
	private function read_location_from_db( $location_id ) {

		global $wpdb;
		$prefix   = $wpdb->prefix;
		$query    = "SELECT * from " . $prefix . "em_locations where location_id =" . $location_id;
		$location = $wpdb->get_results( $query, ARRAY_A );

		// As we expect only one row, we unwrap the inner array, if $location is set.
		if ( isset( $location[0] ) ) {
			$location = $location [0];

			// Change key 'post content' to 'location_description'.
			$location['location_description'] = $location['post_content'];
			unset( $location['post_content'] );

			return $location;
		}

		return false;
	}

	/**
	 * .
	 */
	public function deliver_csv_file() {

		$events = $this->read_events_from_db();
		$events = $this->read_event_attributes( $events );
		$events = $this->add_locations_to_events( $events );
		$events = $this->strip_html_tags( $events );
		$this->download_send_headers( 'em-events' . date( 'm- d- y' ) . '.csv' );
		echo esc_html( $this->array_to_csv( $events ) );
		die;
	}

	/**
	 * @param array $array The array with event data.
	 *
	 * @return null|string
	 */
	protected function array_to_csv( array &$array ) {

		if ( 0 === count( $array ) ) {
			return null;
		}

		ob_start();
		$df = fopen( "php://output", 'w' );

		// set utf-8 encoding.
		fprintf( $df, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
		// write keys of the longest array element to the file.
		fputcsv( $df, array_keys( max( $array ) ), $this->delimiter );
		foreach ( $array as $row ) {
			fputcsv( $df, $row, $this->delimiter );
		}
		fclose( $df );

		return ob_get_clean();
	}

	/**
	 * Sends the headers for the download
	 *
	 * @param string $filename The name of the file to be downloaded.
	 *
	 * @return void
	 */
	protected function download_send_headers( $filename ) {

		// Disable caching.
		header( 'Expires: Tue, 03 Jul 2001 06:00:00 GMT' );
		header( 'Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate' );
		header( 'Last-Modified: {$now} GMT' );

		// Force download.
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Type: application/download' );

		// Dsposition / encoding on response body.
		header( "Content-Disposition: attachment;filename={$filename}" );
		header( 'Content-Transfer-Encoding: binary' );
	}

	/**
	 * Reads the events' custom attributes from post meta.
	 *
	 * @param array $events Array with events manager event data.
	 *
	 * @return array The input array, merged with the data of the custom attributes.
	 */
	private function read_event_attributes( $events ) {
		$attr = em_get_attributes();

		foreach ( $events as $key => $event ) {
			foreach ( $attr['names'] as $attr_name ) {
				$attr_value                   = get_post_meta( $event['post_id'], $attr_name, true );
				$events[ $key ][ $attr_name ] = $attr_value;
			}
		}

		return $events;

	}

	/**
	 * Adds the location array to the event array
	 *
	 * @param array $events The array with events.
	 *
	 * @return array The supplied events array with added location details
	 */
	private function add_locations_to_events( $events ) {

		foreach ( $events as $key => $event ) {
			$location_info   = $this->read_location_from_db( $event['location_id'] );
			$events [ $key ] = is_array( $location_info ) ? array_merge( $event, $location_info ) : $event;

		}

		return $events;
	}

	/**
	 * Strips all HTML Tags from the event array.
	 *
	 * @param array $events The event array to be stripped.
	 *
	 * @return array The supplied array without HTML-Tags
	 */
	private function strip_html_tags( $events ) {

		foreach ( $events as $key => $row ) {
			foreach ( $row as $rowkey => $column ) {
				$events[ $key ][ $rowkey ] = wp_strip_all_tags( $column );
			}
		}

		return $events;

	}

}