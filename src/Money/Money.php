<?php
/**
 * Contains the Money class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Money;

use Wrd\WpObjective\Contracts\Apiable;

/**
 * Represents an amount of money.
 *
 * Some limitations with our money & currency systems.
 *  - Only 1 level of sub-unit currency (e.g. cents) is supported, excluding currencies like Jordanian dinar.
 *  - We do not support non-decimal currency.
 *  - We do not support negative amounts.
 */
class Money implements Apiable {
	/**
	 * The amount of money in the smallest denomination (e.g. cents).
	 *
	 * @var int
	 */
	private int $amount;

	/**
	 * The currency associated with the money amount.
	 *
	 * @var Currency
	 */
	private Currency $currency;

	/**
	 * Create a new Money instance.
	 *
	 * @param int      $amount The amount of money in the smallest denomination (e.g. cents).
	 * @param Currency $currency The currency associated with the money amount.
	 */
	public function __construct( int $amount, Currency $currency ) {
		$this->amount   = $amount;
		$this->currency = $currency;
	}

	/**
	 * Get the amount.
	 *
	 * @return int
	 */
	public function get_amount(): int {
		return $this->amount;
	}

	/**
	 * Get the base unit amount.
	 *
	 * This is the amount in the base unit of the currency (e.g. dollars instead of cents).
	 *
	 * We use string casting to avoid floating point inprecision.
	 *
	 * @return int
	 */
	public function get_base_unit_amount(): int {
		return (int) substr( (string) $this->get_amount(), 0, -1 * $this->currency->get_decimals() );
	}

	/**
	 * Get the sub unit amount.
	 *
	 * This is the amount in the sub unit of the currency (e.g. cents instead of dollars).
	 *
	 * We use string casting to avoid floating point inprecision.
	 *
	 * @return int
	 */
	public function get_sub_unit_amount(): int {
		return (int) substr( (string) $this->get_amount(), -1 * $this->currency->get_decimals() );
	}

	/**
	 * Get the value as a float.
	 *
	 * Due to floating-point inprecision, this is not recommended.
	 *
	 * @return float
	 */
	public function get_float_amount(): float {
		return (float) $this->get_amount() / pow( 10, $this->currency->get_decimals() );
	}

	/**
	 * Get the currency.
	 *
	 * @return Currency
	 */
	public function get_currency(): Currency {
		return $this->currency;
	}

	/**
	 * Format the money using it's currency.
	 *
	 * @return string
	 */
	public function get_formatted(): string {
		return $this->get_currency()->format( $this->get_amount() );
	}

	/**
	 * Convert the address to an API-compatible array.
	 *
	 * @return array<string, mixed>
	 */
	public function to_api(): array {
		return array(
			'amount'   => $this->get_amount(),
			'formats'  => array(
				'base_unit' => $this->get_base_unit_amount(),
				'sub_unit'  => $this->get_sub_unit_amount(),
				'float'     => $this->get_float_amount(),
				'long'      => $this->get_formatted(),
			),
			'currency' => $this->get_currency(),
		);
	}
}
