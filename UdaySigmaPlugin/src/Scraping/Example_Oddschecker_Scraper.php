<?php
namespace WPOddsComparison\Scraping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Example_Oddschecker_Scraper extends Base_Scraper {


	private $base_url = 'https://www.oddschecker.com/';

// enable live scraping = true; oddschecker.com
	private $enable_live_scraping = true;

	private $bookmaker_key;

	public function __construct( $bookmaker_key ) {
		$this->bookmaker_key = $bookmaker_key;
		
		
		if ( defined( 'WPODDS_ENABLE_LIVE_SCRAPING' ) ) {
			$this->enable_live_scraping = (bool) WPODDS_ENABLE_LIVE_SCRAPING;
		}
	}


	public function fetch_odds( $event_slug, array $markets ) {
		$result = array();


		if ( $this->enable_live_scraping ) {
			try {
				// url = $this->
				
				$url = $this->base_url . ltrim( $event_slug, '/' );
				
				
				$html = $this->http_get( $url );
				
				
				$dom = $this->create_dom( $html );
				$xpath = new \DOMXPath( $dom );

				
				foreach ( $markets as $market ) {
					$market_odds = array();

					
					$rows = $xpath->query( '//table//tr' );

					if ( $rows && $rows->length > 0 ) {
						$selection_count = 0;
						
						
						foreach ( $rows as $row ) {
							
							if ( $selection_count >= 2 ) {
								break;
							}

							
							$text = trim( $row->textContent );
							
							
							if ( preg_match_all( '/\d+(\.\d+)?/', $text, $matches ) && ! empty( $matches[0] ) ) {
								
								$decimal_odds = (float) $matches[0][0];
								
								
								$selection_name = ( $selection_count === 0 ) ? 'Selection A' : 'Selection B';
								
								
								$market_odds[ $selection_name ] = $decimal_odds;
								$selection_count++;
							}
						}
					}

					
					if ( ! empty( $market_odds ) ) {
						$result[ $market ] = $market_odds;
					}
				}
			} catch ( \Exception $e ) {
				
			}
		}

		
		if ( empty( $result ) ) {
			foreach ( $markets as $market ) {
				
				$seed = crc32( $event_slug . '|' . $market . '|' . $this->bookmaker_key );
				
				
				$base = 1.50 + ( ( $seed % 60 ) / 100 );

				
				$result[ $market ] = array(
					'Selection A' => round( $base, 2 ),
					'Selection B' => round( max( 1.01, 3.50 - $base ), 2 ),
				);
			}
		}

		return $result;
	}
}

