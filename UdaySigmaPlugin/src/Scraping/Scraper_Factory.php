<?php

namespace WPOddsComparison\Scraping;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scraper_Factory {

	private $bookmakers = array(
		'oddschecker' => array(
			'name'  => 'Oddschecker',
			'key'   => 'oddschecker',
			'class' => Example_Oddschecker_Scraper::class,
		),
		'bookmaker_a' => array('name'=>'Bookmaker A','key'=>'bookmaker_a','class'=>Dummy_Scraper::class),
		'bookmaker_b' => array('name'=>'Bookmaker B','key'=>'bookmaker_b','class'=>Dummy_Scraper::class),
		'bookmaker_c' => array('name'=>'Bookmaker C','key'=>'bookmaker_c','class'=>Dummy_Scraper::class),
		'bookmaker_d' => array('name'=>'Bookmaker D','key'=>'bookmaker_d','class'=>Dummy_Scraper::class),
		'bookmaker_e' => array('name'=>'Bookmaker E','key'=>'bookmaker_e','class'=>Dummy_Scraper::class),
		'bookmaker_f' => array('name'=>'Bookmaker F','key'=>'bookmaker_f','class'=>Dummy_Scraper::class),
		'bookmaker_g' => array('name'=>'Bookmaker G','key'=>'bookmaker_g','class'=>Dummy_Scraper::class),
		'bookmaker_h' => array('name'=>'Bookmaker H','key'=>'bookmaker_h','class'=>Dummy_Scraper::class),
		'bookmaker_i' => array('name'=>'Bookmaker I','key'=>'bookmaker_i','class'=>Dummy_Scraper::class),
		'bookmaker_j' => array('name'=>'Bookmaker J','key'=>'bookmaker_j','class'=>Dummy_Scraper::class),
	);

	public function __construct() {
		$this->bookmakers = apply_filters( 'wpodds_bookmakers', $this->bookmakers );
	}

	public function get_registered_bookmakers() {
		return $this->bookmakers;
	}

	public function create( $bookmaker_id ) {
		if ( ! isset( $this->bookmakers[ $bookmaker_id ] ) ) {
			return null;
		}

		$config = $this->bookmakers[ $bookmaker_id ];
		if ( ! class_exists( $config['class'] ) ) {
			return null;
		}

		return new $config['class']( $config['key'] );
	}
}
