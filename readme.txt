=== Plugin Name ===
Contributors: s-hinse
Donate link: http:///www.svenhinse.de/webdev
Tags: Events Manager, CSV, Event export
Requires at least: 4.0
Tested up to: 5.2.2
Stable tag: 1.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Export events to CSV (Addon for Events Manager Plugin )

== Description ==

 * Addon for the Events Manager Plugin. https://wordpress.org/plugins/events-manager/
 * Exports all events and their locations into a csv file.
 * Choose comma or semicolon as CSV delimiter

## Usage
1. Go to `Events->Export` events as CSV
2. Choose your delimiter
3. Click the "Download"-button to get your CSV file.




== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/export-em-events-to-csv` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin in WordPress backend, make sure you have installed and activated Events Manager first.

== Excluding fields from the CSV export ==
By default, the plugin does not export the following fields:
            'event_id',
			'post_id',
			'event_owner',
			'event_status',
			'blog_id',
			'group_id'

If you want to add or remove fields from the selection, you can use the filter 'em_events_csv_export_unwanted_keys' to modify it.


== Changelog ==
= 1.2.1 =
* run in backend only
* remove blank space in filename that causes errors in some configurations
* set minimum required PHP version to 5.6.
* display errors as admin notice
= 1.2 =
* adapt to fit database structure of current events manager plugin for custom attributes
* add filter to remove unwanted fields from the CSV
* add option to remember the last used delimiter
* fixed bug that caused a php warning
= 1.1.1 =
 * fixed bug where csv output is messed up if first event has no custom attributes
= 1.1 =
 * added choice of delimiter
= 1.0 =
* first version

