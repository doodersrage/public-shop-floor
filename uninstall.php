<?php
/**
 * Uninstall Public Shop Floor.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Drop custom table on uninstall.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'psf_jobs' );
delete_option( 'psf_settings' );
delete_option( 'psf_stations' );
delete_option( 'psf_jobs_db_version' );
delete_option( 'psf_job_seq' );
delete_option( 'psf_flush_rewrites' );
