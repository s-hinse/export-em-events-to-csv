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
	//phpcs:disable WordPress.VIP.DirectDatabaseQuery

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
		$query    = 'SELECT * from ' . $prefix . 'em_locations where location_id =' . $location_id;
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
    $events = $this->add_tag_to_events( $events );
    $events = $this->add_cats_to_events( $events );
		$events = $this->strip_html_tags( $events );
		$this->download_send_headers( 'em-events' . '-' .date( 'ymd-Hi' ) . '.csv' );
		//phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo( $this->array_to_csv( $events ) );
		die;
		//phpcs:enable WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	/** Converts the events array to CSV
	 *
	 * @param array $events The array with event data.
	 *
	 * @return null|string
	 */
	protected function array_to_csv( array $events ) {

		if ( 0 === count( $events ) ) {
			return null;
		}
		$events = $this->remove_unwanted_keys( $events );
		ob_start();
		$df = fopen( "php://output", 'w' );

		// set utf-8 encoding.
		fprintf( $df, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
		// write keys of the longest array element to the file.
		fputcsv( $df, array_keys( max( $events ) ), $this->delimiter );
		foreach ( $events as $event ) {
			fputcsv( $df, $event, $this->delimiter );
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
	 * Adds the taxonomy array to the event array
	 *
	 * @param array $events The array with events.
	 *
	 * @return array The supplied events array with added taxonomy details
	 */
	private function add_tag_to_events( $events ) {

		foreach ( $events as $key => $event ) {
			$term_obj_list   = get_the_terms( $event['post_id'], 'event-tags' );
      $term_array = wp_list_pluck($term_obj_list, 'name');
			$events [ $key ] = is_array( $term_obj_list) ? array_merge( $event, $term_array ) : $event;

		}

		return $events;
	}

  private function add_cats_to_events( $events ) {

		foreach ( $events as $key => $event ) {
			$term_obj_list   = get_the_terms( $event['post_id'], 'event-categories' );
      $term_array = wp_list_pluck($term_obj_list, 'name');
			$events [ $key ] = is_array( $term_obj_list) ? array_merge( $event, $term_array ) : $event;

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

	/**
	 * Removes keys from $events array. Key with keys to remove can be filtered.
	 *
	 * @param array $events The events array.
	 * @wp_filter "em_events_csv_export_unwanted_keys"
	 *
	 * @return array     *
	 */
	private function remove_unwanted_keys( array $events ) {
		$unwanted_keys = [
			'event_id',
			'post_id',
			'event_owner',
			'event_status',
			'blog_id',
			'group_id',
		];
		$unwanted_keys = apply_filters( 'em_events_csv_export_unwanted_keys', $unwanted_keys );
		// Event_attributes need to be removed, therefore they cannot be filtered.
		$unwanted_keys[] = 'event_attributes';
		foreach ( $events as $key => $event ) {
			$events[ $key ] = array_diff_key( $event, array_flip( $unwanted_keys ) );
		}

		return $events;
	}

}