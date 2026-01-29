<?php

namespace WPOddsComparison\Core;

use WPOddsComparison\Admin\Settings_Page;
use WPOddsComparison\Odds\Odds_Service;
use WPOddsComparison\Blocks\Odds_Comparison_Block;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	private static $instance = null;
	private $odds_service;
	private $settings_page;
	private $odds_block;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->odds_service  = new Odds_Service();
		$this->settings_page = new Settings_Page( $this->odds_service );
		$this->odds_block    = new Odds_Comparison_Block( $this->odds_service );
	}

	public function init() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this->settings_page, 'register_settings' ) );
		add_action( 'admin_menu', array( $this->settings_page, 'register_menu' ) );

		$this->odds_block->register();
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-odds-comparison',
			false,
			dirname( plugin_basename( WPODDS_PLUGIN_FILE ) ) . '/languages/'
		);
	}
}
