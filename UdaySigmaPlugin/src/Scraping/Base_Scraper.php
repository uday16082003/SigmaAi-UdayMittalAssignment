<?php

namespace WPOddsComparison\Scraping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Base_Scraper implements Scraper_Interface {

	protected function http_get( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 5,
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			throw new \RuntimeException( 'Unexpected response code: ' . $code );
		}

		return wp_remote_retrieve_body( $response );
	}

	//html bhi add 

	protected function create_dom( $html ) {
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( $html );
		libxml_clear_errors();
		return $dom;
	}

	abstract public function fetch_odds( $event_slug, array $markets );
}
