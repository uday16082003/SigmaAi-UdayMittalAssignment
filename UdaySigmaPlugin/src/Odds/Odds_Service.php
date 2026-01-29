<?php

namespace WPOddsComparison\Odds;

use WPOddsComparison\Scraping\Scraper_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Odds_Service {

	private $cache_ttl = 60;
	private $scraper_factory;
	private $odds_converter;

	public function __construct() {
		$this->scraper_factory = new Scraper_Factory();
		$this->odds_converter  = new Odds_Converter();
	}

	public function get_available_bookmakers() {
		return $this->scraper_factory->get_registered_bookmakers();
	}

	public function get_odds( $event_slug, array $bookmakers, array $markets, $odds_format = 'decimal' ) {
		$cache_key = $this->build_cache_key( $event_slug, $bookmakers, $markets, $odds_format );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$data = array();

		foreach ( $bookmakers as $bookmaker_id ) {
			$scraper = $this->scraper_factory->create( $bookmaker_id );
			if ( ! $scraper ) {
				continue;
			}

			try {
				$bookmaker_odds = $scraper->fetch_odds( $event_slug, $markets );
			} catch ( \Exception $e ) {
				continue;
			}

			foreach ( $bookmaker_odds as $market => $selection_odds ) {
				foreach ( $selection_odds as $selection => $odds_value ) {
					$converted = $this->odds_converter->convert_to_format( $odds_value, $odds_format );
					$data[ $market ][ $selection ][ $bookmaker_id ] = array(
						'raw'       => $odds_value,
						'formatted' => $converted,
					);
				}
			}
		}

		set_transient( $cache_key, $data, $this->cache_ttl );

		return $data;
	}

	private function build_cache_key( $event_slug, array $bookmakers, array $markets, $format ) {
		sort( $bookmakers );
		sort( $markets );
		$hash_components = array(
			$event_slug,
			implode( ',', $bookmakers ),
			implode( ',', $markets ),
			$format,
		);
		return 'wpodds_' . md5( implode( '|', $hash_components ) );
	}
}
