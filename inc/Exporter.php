<?php
/**
 *Exporter class for export-em-events-to-csv
 *
 * @author: s-hinse
 */

namespace SHinse\ExportEMEventsToCSV\inc;

/**
 * Class Exporter
 *
 * @package SHinse\ExportEMEventsToCSV\inc
 */
class Exporter {

	/**
	 * @var string The delimiter to use in the csv file
	 */

	private $delimiter;

	/**
	 * @return string
	 */
	public function get_delimiter() {

		return $this->delimiter;
	}

	/**
	 * @param string $delimiter
	 */
	public function set_delimiter( $delimiter ) {

		$this->delimiter = $delimiter;
	}

	/**
	 * reads all events from the database
	 *
	 * @return array  Array with events
	 */
	private function read_events_from_db() {

		global $wpdb;
		$prefix = $wpdb->prefix;
		$query  = "SELECT * FROM " . $prefix . "em_events e  ";

		$events = $wpdb->get_results( $query, ARRAY_A );

		return $events;

	}

	/**
	 * returns an array with the the location details for the given location id
	 *
	 * @param $location_id
	 *
	 * @return array  Associative array with the db row of the location with $location_id
	 */
	private function read_location_from_db( $location_id ) {

		global $wpdb;
		$prefix   = $wpdb->prefix;
		$query    = "SELECT * from " . $prefix . "em_locations where location_id =" . $location_id;
		$location = $wpdb->get_results( $query, ARRAY_A );

		//as we expect only one row, we unwrap the inner array, if $location is set
		if ( isset($location[ 0 ]) ) {
			$location = $location [ 0 ];

			//change key 'post content' to 'location_description'
			$location[ 'location_description' ] = $location[ 'post_content' ];
			unset ( $location[ 'post_content' ] );

			return $location;
		}
	return false;
	}

	/**
	 *gets event data, unserializes them and provides file to download
	 */
	public function deliver_csv_file() {

		$events = $this->read_events_from_db();
		$events = $this->unserialize_event_attributes( $events );
		$events = $this->add_locations_to_events( $events );
		$events = $this->strip_html_tags( $events );
		$this->download_send_headers( "em-events" . date("m . d . y ") . ".csv" );
		echo $this->array_to_csv( $events );
		die;
	}

	/**
	 * @param array $array The array with event data
	 *
	 * @return null|string
	 */
	protected function array_to_csv( array &$array ) {

		if ( count( $array ) == 0 ) {
			return NULL;
		}

		ob_start();
		$df = fopen( "php://output", 'w' );

		//set utf-8 encoding
		fprintf( $df, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
		//write keys of the longest array element to the file
		fputcsv( $df, array_keys( max( $array ) ), $this->delimiter );
		foreach ( $array as $row ) {
			fputcsv( $df, $row, $this->delimiter );
		}
		fclose( $df );

		return ob_get_clean();
	}

	/**
	 * @param $filename The name of the file to be downloaded
	 *
	 * @return null
	 */
	protected function download_send_headers( $filename ) {

		// disable caching
		$now = gmdate( "D, d M Y H:i:s" );
		header( "Expires: Tue, 03 Jul 2001 06:00:00 GMT" );
		header( "Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate" );
		header( "Last-Modified: {$now} GMT" );

		// force download
		header( "Content-Type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Type: application/download" );

		// disposition / encoding on response body
		header( "Content-Disposition: attachment;filename={$filename}" );
		header( "Content-Transfer-Encoding: binary" );
	}

	/**
	 * @param $events Array with events manager event data
	 *
	 * @return array The input array, merged with the unserialized data of 'event_attributes'
	 */
	protected function unserialize_event_attributes( $events ) {

		foreach ( $events as $key => $row ) {

			if ( isset( $row [ 'event_attributes' ] ) ) {
				$event_attributes = unserialize( $row[ 'event_attributes' ] );
				//sort elements by key to get always the same order
				ksort( $event_attributes );
				$events [ $key ] = is_array( $event_attributes ) ? array_merge( $row, $event_attributes ) : $row;
				//delete the serialized version
				unset( $events[ $key ] [ 'event_attributes' ] );
			}

		}

		return $events;

	}

	/**
	 * @param $events
	 *
	 * @return array The supplied events array with added location details
	 */
	private function add_locations_to_events( $events ) {

		foreach ( $events as $key => $row ) {
			$location_info   = $this->read_location_from_db( $row[ 'location_id' ] );
			$events [ $key ] = is_array( $location_info ) ? array_merge( $row, $location_info ) : $row;

		}

		return $events;
	}

	/**
	 * @param array $events
	 *
	 * @return Array The supplied array without HTML-Tags
	 */
	private function strip_html_tags( $events ) {

		foreach ( $events as $key => $row ) {
			foreach ( $row as $rowkey => $column ) {
				$events[ $key ][ $rowkey ] = strip_tags( $column );
			}
		}

		return $events;

	}

}