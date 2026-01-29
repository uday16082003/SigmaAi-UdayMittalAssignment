<?php

namespace WPOddsComparison\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Autoloader {

	private static $base_namespace;
	private static $base_dir;

	public static function init( $base_namespace, $base_dir ) {
		self::$base_namespace = trim( $base_namespace, '\\' );
		self::$base_dir = rtrim( $base_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	public static function autoload( $class ) {

		if ( empty( self::$base_namespace ) || empty( self::$base_dir ) ) {
			return;
		}

		if ( strpos( $class, self::$base_namespace . '\\' ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, strlen( self::$base_namespace . '\\' ) );
		$file           = self::$base_dir
			. str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class )
			. '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
