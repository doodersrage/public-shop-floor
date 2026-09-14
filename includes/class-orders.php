<?php
/**
 * Create jobs when made-to-order orders start work.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSF_Orders {

	public static function init() {
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'open_jobs' ) );
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'open_jobs' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'open_jobs' ) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( __CLASS__, 'order_panel' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'account_jobs' ) );
		add_filter( 'woocommerce_email_order_meta', array( __CLASS__, 'email_jobs' ), 10, 3 );
	}

	public static function open_jobs( $order_id ) {
		PSF_Jobs::create_for_order( $order_id );
	}

	public static function order_panel( $order ) {
		$jobs = PSF_Jobs::for_order( $order->get_id() );
		if ( ! $jobs ) {
			return;
		}
		echo '<div class="psf-order-jobs"><h3>Shop floor</h3><ul>';
		foreach ( $jobs as $job ) {
			$station = PSF_Stations::get( $job['station_key'] );
			$label   = $station ? $station['label'] : $job['station_key'];
			$url     = home_url( '/shop-floor/job/' . $job['token'] . '/' );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $job['job_number'] ) . '</a> — ' . esc_html( $label );
			if ( 'held' === $job['status'] ) {
				echo ' (held)';
			}
			if ( 'complete' === $job['status'] ) {
				echo ' (left the floor)';
			}
			echo '</li>';
		}
		echo '</ul></div>';
	}

	public static function account_jobs( $order ) {
		$jobs = PSF_Jobs::for_order( $order->get_id() );
		if ( ! $jobs ) {
			return;
		}
		echo '<section class="psf-account-jobs"><h2>On the floor</h2><ul>';
		foreach ( $jobs as $job ) {
			$station = PSF_Stations::get( $job['station_key'] );
			$url     = home_url( '/shop-floor/job/' . $job['token'] . '/' );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $job['job_number'] ) . '</a> — ';
			echo esc_html( $station ? $station['verb'] : $job['station_key'] );
			echo '</li>';
		}
		echo '</ul></section>';
	}

	public static function email_jobs( $order, $sent_to_admin, $plain ) {
		unset( $sent_to_admin, $plain );
		$jobs = PSF_Jobs::for_order( $order->get_id() );
		if ( ! $jobs ) {
			return;
		}
		echo '<p>Watch this job on the shop floor:</p><ul>';
		foreach ( $jobs as $job ) {
			$url = home_url( '/shop-floor/job/' . $job['token'] . '/' );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $job['job_number'] ) . '</a></li>';
		}
		echo '</ul>';
	}
}
