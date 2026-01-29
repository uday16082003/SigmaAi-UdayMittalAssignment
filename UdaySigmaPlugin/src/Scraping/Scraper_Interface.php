<?php

namespace WPOddsComparison\Scraping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Scraper_Interface {
	public function fetch_odds( $event_slug, array $markets );
}
