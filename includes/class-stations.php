<?php
/**
 * Station pipeline.
 *
 * @package PublicShopFloor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PSF_Stations {

	public static function defaults() {
		return array(
			array(
				'key'   => 'mill',
				'label' => __( 'Mill', 'public-shop-floor' ),
				'verb'  => __( 'Being milled', 'public-shop-floor' ),
			),
			array(
				'key'   => 'joinery',
				'label' => __( 'Joinery', 'public-shop-floor' ),
				'verb'  => __( 'In joinery', 'public-shop-floor' ),
			),
			array(
				'key'   => 'finish',
				'label' => __( 'Finish', 'public-shop-floor' ),
				'verb'  => __( 'In finish', 'public-shop-floor' ),
			),
			array(
				'key'   => 'packed',
				'label' => __( 'Packed', 'public-shop-floor' ),
				'verb'  => __( 'Ready to leave', 'public-shop-floor' ),
			),
		);
	}

	public static function all() {
		$saved = get_option( 'psf_stations', array() );
		if ( ! is_array( $saved ) || empty( $saved ) ) {
			return self::defaults();
		}
		$out = array();
		foreach ( $saved as $row ) {
			if ( empty( $row['key'] ) || empty( $row['label'] ) ) {
				continue;
			}
			$out[] = array(
				'key'   => sanitize_key( $row['key'] ),
				'label' => sanitize_text_field( $row['label'] ),
				'verb'  => sanitize_text_field( $row['verb'] ?? $row['label'] ),
			);
		}
		return $out ? $out : self::defaults();
	}

	public static function keys() {
		return wp_list_pluck( self::all(), 'key' );
	}

	public static function first_key() {
		$keys = self::keys();
		return $keys ? $keys[0] : 'mill';
	}

	public static function last_key() {
		$keys = self::keys();
		return $keys ? $keys[ count( $keys ) - 1 ] : 'packed';
	}

	public static function next_key( $current ) {
		$keys = self::keys();
		$i    = array_search( $current, $keys, true );
		if ( false === $i || ! isset( $keys[ $i + 1 ] ) ) {
			return null;
		}
		return $keys[ $i + 1 ];
	}

	public static function prev_key( $current ) {
		$keys = self::keys();
		$i    = array_search( $current, $keys, true );
		if ( false === $i || $i < 1 ) {
			return null;
		}
		return $keys[ $i - 1 ];
	}

	public static function get( $key ) {
		foreach ( self::all() as $station ) {
			if ( $station['key'] === $key ) {
				return $station;
			}
		}
		return null;
	}

	public static function save_from_text( $text ) {
		$lines = preg_split( '/\R/', (string) $text ) ?: array();
		$rows  = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || str_starts_with( $line, '#' ) ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			$label = $parts[0] ?? '';
			if ( '' === $label ) {
				continue;
			}
			$key = sanitize_key( $parts[1] ?? $label );
			if ( '' === $key ) {
				continue;
			}
			$rows[] = array(
				'key'   => $key,
				'label' => sanitize_text_field( $label ),
				'verb'  => sanitize_text_field( $parts[2] ?? $label ),
			);
		}
		if ( $rows ) {
			update_option( 'psf_stations', $rows );
		}
		return $rows;
	}

	public static function to_text() {
		$lines = array();
		foreach ( self::all() as $station ) {
			$lines[] = $station['label'] . ' | ' . $station['key'] . ' | ' . $station['verb'];
		}
		return implode( "\n", $lines );
	}
}
