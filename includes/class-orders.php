<?php
/**
 * Create jobs when made-to-order orders start work.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSFloor_Orders {

	public static function init() {
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'open_jobs' ) );
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'open_jobs' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'open_jobs' ) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( __CLASS__, 'order_panel' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'account_jobs' ) );
		add_action( 'woocommerce_email_order_meta', array( __CLASS__, 'email_jobs' ), 10, 3 );
	}

	public static function open_jobs( $order_id ) {
		PSFloor_Jobs::create_for_order( $order_id );
	}

	public static function order_panel( $order ) {
		$jobs = PSFloor_Jobs::for_order( $order->get_id() );
		if ( ! $jobs ) {
			return;
		}
		echo '<div class="psf-order-jobs"><h3>' . esc_html__( 'Shop floor', 'public-shop-floor' ) . '</h3><ul>';
		foreach ( $jobs as $job ) {
			$station = PSFloor_Stations::get( $job['station_key'] );
			$label   = $station ? $station['label'] : $job['station_key'];
			$url     = home_url( '/shop-floor/job/' . $job['token'] . '/' );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $job['job_number'] ) . '</a> — ' . esc_html( $label );
			if ( 'held' === $job['status'] ) {
				echo ' ' . esc_html__( '(held)', 'public-shop-floor' );
			}
			if ( 'complete' === $job['status'] ) {
				echo ' ' . esc_html__( '(left the floor)', 'public-shop-floor' );
			}
			echo '</li>';
		}
		echo '</ul></div>';
	}

	public static function account_jobs( $order ) {
		$jobs = PSFloor_Jobs::for_order( $order->get_id() );
		if ( ! $jobs ) {
			return;
		}
		echo '<section class="psf-account-jobs"><h2>' . esc_html__( 'On the floor', 'public-shop-floor' ) . '</h2><ul>';
		foreach ( $jobs as $job ) {
			$station = PSFloor_Stations::get( $job['station_key'] );
			$url     = home_url( '/shop-floor/job/' . $job['token'] . '/' );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $job['job_number'] ) . '</a> — ';
			echo esc_html( $station ? $station['verb'] : $job['station_key'] );
			echo '</li>';
		}
		echo '</ul></section>';
	}

	public static function email_jobs( $order, $sent_to_admin, $plain_text ) {
		unset( $sent_to_admin );
		$jobs = PSFloor_Jobs::for_order( $order->get_id() );
		if ( ! $jobs ) {
			return;
		}

		$heading = __( 'Watch this job on the shop floor:', 'public-shop-floor' );

		if ( $plain_text ) {
			echo "\n" . esc_html( $heading ) . "\n\n";
			foreach ( $jobs as $job ) {
				$url = home_url( '/shop-floor/job/' . $job['token'] . '/' );
				echo esc_html( $job['job_number'] ) . ': ' . esc_url( $url ) . "\n";
			}
			echo "\n";
			return;
		}

		echo '<p>' . esc_html( $heading ) . '</p><ul>';
		foreach ( $jobs as $job ) {
			$url = home_url( '/shop-floor/job/' . $job['token'] . '/' );
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $job['job_number'] ) . '</a></li>';
		}
		echo '</ul>';
	}
}
