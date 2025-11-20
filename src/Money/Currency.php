<?php
/**
 * Contains the Currency class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Money;

use Wrd\WpObjective\Contracts\Apiable;

/**
 * Represents an currency.
 */
class Currency implements Apiable {
	/**
	 * Currency code (e.g. USD, EUR).
	 *
	 * @var string
	 */
	private string $code;

	/**
	 * Human readable name of the currency.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Currency symbol (e.g. $, €).
	 *
	 * @var string
	 */
	private string $symbol;

	/**
	 * Whether the currency symbol should be placed before the amount, or after.
	 *
	 * Expects 'before' or 'after'.
	 *
	 * @var string
	 */
	private string $symbol_position = 'before';

	/**
	 * Number of decimal places.
	 *
	 * @var int
	 */
	private int $decimals = 2;

	/**
	 * Decimal separator character.
	 *
	 * @var string
	 */
	private string $decimal_separator = '.';

	/**
	 * Thousands separator character.
	 *
	 * @var string
	 */
	private string $thousands_separator = ',';

	/**
	 * Get the USD currency instance.
	 *
	 * @return Currency
	 */
	public static function USD(): Currency {
		return new Currency( 'USD', 'United States Dollar', '$', 'before', 2, '.', ',' );
	}

	/**
	 * Get the GBP currency instance.
	 *
	 * @return Currency
	 */
	public static function GBP(): Currency {
		return new Currency( 'GBP', 'British Pound', '£', 'before', 2, '.', ',' );
	}

	/**
	 * Get the EUR currency instance.
	 *
	 * @return Currency
	 */
	public static function EUR(): Currency {
		return new Currency( 'EUR', 'Euro', '€', 'before', 2, '.', ',' );
	}

	/**
	 * Create a new currency instance.
	 *
	 * @param string $code Currency code (e.g. USD, EUR).
	 * @param string $name Human readable name of the currency.
	 * @param string $symbol Currency symbol (e.g. $, €).
	 * @param string $symbol_position Whether the currency symbol should be placed before the amount, or after. Optional, defaults to 'before'. Expects 'before' or 'after'.
	 * @param int    $decimals Number of decimal places. Optional, defaults to 2.
	 * @param string $decimal_separator Decimal separator character. Optional, defaults to '.'.
	 * @param string $thousands_separator Thousands separator character. Optional, defaults to ','.
	 */
	public function __construct(
		string $code,
		string $name,
		string $symbol,
		string $symbol_position = 'before',
		int $decimals = 2,
		string $decimal_separator = '.',
		string $thousands_separator = ','
	) {
		$this->code                = $code;
		$this->name                = $name;
		$this->symbol              = $symbol;
		$this->symbol_position     = $symbol_position;
		$this->decimals            = $decimals;
		$this->decimal_separator   = $decimal_separator;
		$this->thousands_separator = $thousands_separator;
	}

	/**
	 * Get the currency code.
	 *
	 * @return string
	 */
	public function get_code(): string {
		return $this->code;
	}

	/**
	 * Get the currency's human readable name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the number of decimal places.
	 *
	 * @return int
	 */
	public function get_decimals(): int {
		return $this->decimals;
	}

	/**
	 * Format an amount of money.
	 *
	 * @param int $amount The amount to format, in the currencies lowest denomination (e.g. cents).
	 *
	 * @return string
	 */
	public function format( int $amount ): string {
		$float     = $amount / pow( 10, $this->decimals );
		$formatted = number_format( $float, $this->decimals, $this->decimal_separator, $this->thousands_separator );

		if ( 'before' === $this->symbol_position ) {
			return $this->symbol . $formatted;
		} else {
			return $formatted . $this->symbol;
		}
	}

	/**
	 * Convert the address to an API-compatible array.
	 *
	 * @return array<string, string|int>
	 */
	public function to_api(): array {
		return array(
			'code'                => $this->get_code(),
			'name'                => $this->get_name(),
			'symbol'              => $this->symbol,
			'symbol_position'     => $this->symbol_position,
			'decimals'            => $this->decimals,
			'decimal_separator'   => $this->decimal_separator,
			'thousands_separator' => $this->thousands_separator,
		);
	}

	/**
	 * Parse a string into a value of money.
	 *
	 * @param string $value The value to parse.
	 *
	 * @return Money
	 */
	public function parse( string $value ): Money {
		$dec_sep      = preg_quote( $this->decimal_separator, '/' );
		$numeric_only = preg_replace( "/[^0-9$dec_sep]/", '', $value );

		list( $after, $before ) = explode( $this->decimal_separator, $numeric_only, 2 );

		if ( ! $after ) {
			$after = 0;
		}
		$after = (int) $after;

		if ( ! $before ) {
			$before = 0;
		}
		$before = (int) $before;

		if ( $before > pow( 10, $this->decimals ) ) {
			// Decimal component is too large.
			$before = (int) substr( $before, 0, $this->decimals );
		}

		$amount = $after * pow( 10, $this->decimals ) + $before;

		return new Money( $amount, $this );
	}
}
