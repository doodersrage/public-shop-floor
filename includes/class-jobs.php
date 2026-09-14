<?php
/**
 * Shop-floor jobs.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSF_Jobs {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'psf_jobs';
	}

	public static function maybe_install() {
		if ( get_option( 'psf_jobs_db_version' ) === '1' ) {
			return;
		}
		self::install();
	}

	public static function install() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL,
			order_item_id bigint(20) unsigned NOT NULL DEFAULT 0,
			product_id bigint(20) unsigned NOT NULL DEFAULT 0,
			job_number varchar(32) NOT NULL,
			token varchar(64) NOT NULL,
			station_key varchar(64) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'queued',
			note text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY job_number (job_number),
			UNIQUE KEY token (token),
			KEY order_id (order_id),
			KEY station_status (station_key, status)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'psf_jobs_db_version', '1' );
	}

	public static function create_for_order( $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order );
		}
		if ( ! $order ) {
			return array();
		}

		$created = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();
			if ( ! $product || ! PSF_Product::is_on_floor( $product ) ) {
				continue;
			}
			$existing = self::get_by_item( (int) $order->get_id(), (int) $item_id );
			if ( $existing ) {
				continue;
			}
			$qty = max( 1, (int) $item->get_quantity() );
			for ( $i = 0; $i < $qty; $i++ ) {
				$created[] = self::insert(
					array(
						'order_id'      => (int) $order->get_id(),
						'order_item_id' => (int) $item_id,
						'product_id'    => (int) $product->get_id(),
					)
				);
			}
		}
		return $created;
	}

	public static function insert( $args ) {
		global $wpdb;
		$now    = current_time( 'mysql' );
		$number = self::next_job_number();
		$data   = array(
			'order_id'      => (int) $args['order_id'],
			'order_item_id' => (int) ( $args['order_item_id'] ?? 0 ),
			'product_id'    => (int) ( $args['product_id'] ?? 0 ),
			'job_number'    => $number,
			'token'         => bin2hex( random_bytes( 12 ) ),
			'station_key'   => sanitize_key( $args['station_key'] ?? PSF_Stations::first_key() ),
			'status'        => sanitize_key( $args['status'] ?? 'queued' ),
			'note'          => sanitize_textarea_field( $args['note'] ?? '' ),
			'created_at'    => $args['created_at'] ?? $now,
			'updated_at'    => $args['updated_at'] ?? $now,
		);
		$wpdb->insert( self::table(), $data );
		$data['id'] = (int) $wpdb->insert_id;
		return $data;
	}

	public static function next_job_number() {
		$n = (int) get_option( 'psf_job_seq', 1040 );
		$n++;
		update_option( 'psf_job_seq', $n, false );
		return 'PSF-' . $n;
	}

	public static function get( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ), ARRAY_A );
		return $row ?: null;
	}

	public static function get_by_token( $token ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE token = %s', sanitize_text_field( $token ) ),
			ARRAY_A
		);
		return $row ?: null;
	}

	public static function get_by_item( $order_id, $item_id ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' WHERE order_id = %d AND order_item_id = %d LIMIT 1',
				$order_id,
				$item_id
			),
			ARRAY_A
		);
		return $row ?: null;
	}

	public static function for_order( $order_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' WHERE order_id = %d ORDER BY id ASC',
				$order_id
			),
			ARRAY_A
		) ?: array();
	}

	public static function on_floor() {
		global $wpdb;
		$table = self::table();
		$rows  = $wpdb->get_results(
			"SELECT * FROM {$table} WHERE status IN ('queued','in_station','held') ORDER BY updated_at ASC, id ASC",
			ARRAY_A
		);
		return $rows ?: array();
	}

	public static function at_station( $station_key ) {
		$out = array();
		foreach ( self::on_floor() as $job ) {
			if ( $job['station_key'] === $station_key ) {
				$out[] = $job;
			}
		}
		return $out;
	}

	public static function place_in_line( $job ) {
		$ahead = 0;
		foreach ( self::at_station( $job['station_key'] ) as $other ) {
			if ( (int) $other['id'] === (int) $job['id'] ) {
				break;
			}
			if ( 'held' === $other['status'] ) {
				continue;
			}
			$ahead++;
		}
		return $ahead;
	}

	public static function update( $id, $fields ) {
		global $wpdb;
		$allowed = array( 'station_key', 'status', 'note', 'created_at', 'updated_at' );
		$data    = array();
		foreach ( $allowed as $key ) {
			if ( array_key_exists( $key, $fields ) ) {
				if ( 'note' === $key ) {
					$data[ $key ] = sanitize_textarea_field( $fields[ $key ] );
				} elseif ( in_array( $key, array( 'created_at', 'updated_at' ), true ) ) {
					$data[ $key ] = sanitize_text_field( $fields[ $key ] );
				} else {
					$data[ $key ] = sanitize_key( $fields[ $key ] );
				}
			}
		}
		if ( empty( $data['updated_at'] ) ) {
			$data['updated_at'] = current_time( 'mysql' );
		}
		$wpdb->update( self::table(), $data, array( 'id' => (int) $id ) );
		return self::get( $id );
	}

	public static function advance( $id ) {
		$job = self::get( $id );
		if ( ! $job ) {
			return null;
		}
		$next = PSF_Stations::next_key( $job['station_key'] );
		if ( ! $next ) {
			return self::update( $id, array( 'status' => 'complete' ) );
		}
		$status = ( PSF_Stations::last_key() === $next ) ? 'queued' : 'in_station';
		if ( PSF_Stations::last_key() === $next ) {
			$status = 'queued';
		}
		return self::update(
			$id,
			array(
				'station_key' => $next,
				'status'      => 'in_station',
			)
		);
	}

	public static function send_back( $id ) {
		$job = self::get( $id );
		if ( ! $job ) {
			return null;
		}
		$prev = PSF_Stations::prev_key( $job['station_key'] );
		if ( ! $prev ) {
			return $job;
		}
		return self::update(
			$id,
			array(
				'station_key' => $prev,
				'status'      => 'queued',
			)
		);
	}

	public static function hold( $id ) {
		return self::update( $id, array( 'status' => 'held' ) );
	}

	public static function resume( $id ) {
		return self::update( $id, array( 'status' => 'in_station' ) );
	}

	public static function complete( $id ) {
		$last = PSF_Stations::last_key();
		return self::update(
			$id,
			array(
				'station_key' => $last,
				'status'      => 'complete',
			)
		);
	}

	public static function product_title( $job ) {
		$product = wc_get_product( (int) $job['product_id'] );
		return $product ? $product->get_name() : 'Job';
	}

	public static function age_label( $job ) {
		$ts = strtotime( $job['updated_at'] );
		if ( ! $ts ) {
			return '';
		}
		return human_time_diff( $ts, current_time( 'timestamp' ) ) . ' at this station';
	}
}
