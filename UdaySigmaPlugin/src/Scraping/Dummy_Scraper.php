<?php

namespace WPOddsComparison\Scraping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dummy_Scraper extends Base_Scraper {

	private $bookmaker_key;

	public function __construct( $bookmaker_key ) {
		$this->bookmaker_key = $bookmaker_key;
	}

	public function fetch_odds( $event_slug, array $markets ) {
		$result = array();

		foreach ( $markets as $market ) {
			$seed = crc32( $event_slug . '|' . $market . '|' . $this->bookmaker_key );
			$base_odds = 1.50 + ( ( $seed % 60 ) / 100 );
			$result[ $market ] = array(
				'Selection A' => round( $base_odds, 2 ),
				'Selection B' => round( max( 1.01, 3.50 - $base_odds ), 2 ),
			);
		}

		return $result;
	}
}
