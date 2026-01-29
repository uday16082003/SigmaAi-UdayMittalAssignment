<?php
/**
 * Plugin Name: WP Odds Comparison by Uday Mittal
 * Description: Compare live betting odds from multiple bookmakers.
 * Version: 1
 * Author: Uday Mittal
 */

defined( 'ABSPATH' ) || exit;

define( 'WPODDS_VERSION', '1.0.0' );
define( 'WPODDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once WPODDS_PLUGIN_DIR . 'src/Core/Autoloader.php';

WPOddsComparison\Core\Autoloader::init(
	'WPOddsComparison',
	WPODDS_PLUGIN_DIR . 'src'
);

add_action( 'plugins_loaded', function () {
	WPOddsComparison\Core\Plugin::get_instance()->init();
} );
