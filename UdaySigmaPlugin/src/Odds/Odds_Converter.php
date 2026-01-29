<?php

namespace WPOddsComparison\Odds;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Odds_Converter {

	public function convert_to_format( $value, $format ) {
		$format = strtolower( $format );
		$decimal = $this->to_decimal( $value );

		switch ( $format ) {
			case 'fractional':
				return $this->decimal_to_fractional( $decimal );
			case 'american':
				return $this->decimal_to_american( $decimal );
			case 'decimal':
			default:
				return number_format( (float) $decimal, 2, '.', '' );
		}
	}

	public function to_decimal( $value ) {
		if ( is_numeric( $value ) ) {
			$numeric = (float) $value;
			if ( $numeric >= 1.01 && $numeric <= 1000 ) {
				return $numeric;
			}
			return $this->american_to_decimal( $numeric );
		}

		if ( is_string( $value ) && false !== strpos( $value, '/' ) ) {
			return $this->fractional_to_decimal( $value );
		}

		return (float) $value;
	}

	public function fractional_to_decimal( $fraction ) {
		$parts = explode( '/', $fraction );
		if ( 2 !== count( $parts ) ) {
			return 0.0;
		}

		$num = (float) $parts[0];
		$den = (float) $parts[1];

		if ( $den <= 0 ) {
			return 0.0;
		}

		return ( $num / $den ) + 1.0;
	}

	public function american_to_decimal( $american ) {
		$american = (float) $american;
		if ( 0 === $american ) {
			return 0.0;
		}

		if ( $american > 0 ) {
			return ( $american / 100 ) + 1.0;
		}

		return ( 100 / abs( $american ) ) + 1.0;
	}

	public function decimal_to_fractional( $decimal ) {
		$decimal = (float) $decimal;
		if ( $decimal <= 1.0 ) {
			return '0/1';
		}

		$implied = $decimal - 1.0;
		$precision = 1000;
		$numerator = round( $implied * $precision );
		$denominator = $precision;

		$gcd = $this->gcd( $numerator, $denominator );
		$numerator   = $numerator / $gcd;
		$denominator = $denominator / $gcd;

		return sprintf( '%d/%d', (int) $numerator, (int) $denominator );
	}

	public function decimal_to_american( $decimal ) {
		$decimal = (float) $decimal;
		if ( $decimal <= 1.0 ) {
			return '0';
		}

		if ( $decimal >= 2.0 ) {
			$american = ( $decimal - 1.0 ) * 100;
		} else {
			$american = -100 / ( $decimal - 1.0 );
		}

		return (string) round( $american );
	}

	private function gcd( $a, $b ) {
		$a = abs( (int) $a );
		$b = abs( (int) $b );

		if ( 0 === $b ) {
			return $a;
		}

		while ( 0 !== $b ) {
			$tmp = $b;
			$b   = $a % $b;
			$a   = $tmp;
		}

		return $a;
	}
}
