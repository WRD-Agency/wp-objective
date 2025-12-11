<?php
/**
 * Contains the Address class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

use Wrd\WpObjective\Contracts\Apiable;

/**
 * Represents a physical address.
 *
 * This class is not designed to be an exhausing representation for postal delivery, but for basic representations such as business addresses.
 *
 * Based on the HTML Autocomplete spec. @see https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/autocomplete#street-address
 */
class Address implements Apiable {
	/**
	 * Address line 1.
	 *
	 * A street address. When combined with address lines 2 & 3, this should fully identify the location of the address within its second administrative level (typically a city or town), but should not include the city name, ZIP or postal code, or country name.
	 *
	 * @var ?string
	 */
	public ?string $address_line_1;

	/**
	 * Address line 2.
	 *
	 * @var ?string
	 */
	public ?string $address_line_2;

	/**
	 * Address line 3.
	 *
	 * @var ?string
	 */
	public ?string $address_line_3;

	/**
	 * The first administrative level in the address. This is typically the province in which the address is located. In the United States, this would be the state. In Switzerland, the canton. In the United Kingdom, the county.
	 *
	 * @var ?string
	 */
	public ?string $administrative_level_1;

	/**
	 * The second administrative level, in addresses with at least two of them. In countries with two administrative levels, this would typically be the city, town, village, or other locality in which the address is located.
	 *
	 * @var ?string
	 */
	public ?string $administrative_level_2;

	/**
	 * A country or territory code.
	 *
	 * @var ?string
	 */
	public ?string $country_code;

	/**
	 * A postal code (in the United States, this is the ZIP code).
	 *
	 * @var ?string
	 */
	public ?string $postal_code;

	/**
	 * The latitude of this address.
	 *
	 * @var ?string
	 */
	public ?string $lat = null;

	/**
	 * The longitude of this address.
	 *
	 * @var ?string
	 */
	public ?string $long = null;

	/**
	 * Create a new address.
	 *
	 * @param ?string   $address_line_1 Address line 1.
	 * @param ?string   $address_line_2 Address line 2.
	 * @param ?string   $address_line_3 Address line 3.
	 * @param ?string   $administrative_level_1 The first administrative level in the address. This is typically the province in which the address is located. In the United States, this would be the state. In Switzerland, the canton. In the United Kingdom, the county.
	 * @param ?string   $administrative_level_2 The second administrative level in the address. This is typically the city or town in which the address is located.
	 * @param ?string   $country_code The country code for the address.
	 * @param ?string   $postal_code The postal code for the address.
	 * @param ?string[] $coordinates The coordinates. Array with keys 'lat' and 'long'.
	 */
	public function __construct( ?string $address_line_1 = null, ?string $address_line_2 = null, ?string $address_line_3 = null, ?string $administrative_level_1 = null, ?string $administrative_level_2 = null, ?string $country_code = null, ?string $postal_code = null, ?array $coordinates = null ) {
		$this->address_line_1         = $address_line_1;
		$this->address_line_2         = $address_line_2;
		$this->address_line_3         = $address_line_3;
		$this->administrative_level_1 = $administrative_level_1;
		$this->administrative_level_2 = $administrative_level_2;
		$this->country_code           = $country_code;
		$this->postal_code            = $postal_code;

		if ( is_array( $coordinates ) && array_key_exists( 'lat', $coordinates ) ) {
			$this->lat = $coordinates['lat'];
		}
		if ( is_array( $coordinates ) && array_key_exists( 'long', $coordinates ) ) {
			$this->long = $coordinates['long'];
		}
	}

	/**
	 * Get the full address as a string.
	 *
	 * @return string
	 */
	public function get_full_address(): string {
		$components = array();

		$components[] = $this->address_line_1;

		if ( $this->address_line_2 ) {
			$components[] = $this->address_line_2;
		}
		if ( $this->address_line_3 ) {
			$components[] = $this->address_line_3;
		}

		if ( $this->administrative_level_2 ) {
			$components[] = $this->administrative_level_2;
		}

		if ( $this->administrative_level_1 ) {
			$components[] = $this->administrative_level_1;
		}

		if ( $this->country_code ) {
			$components[] = $this->country_code;
		}

		if ( $this->postal_code ) {
			$components[] = $this->postal_code;
		}

		return implode( ', ', $components );
	}

	/**
	 * Convert the address to an API-compatible array.
	 *
	 * @return array<string, string|null>
	 */
	public function to_api(): array {
		return array(
			'address_line_1'         => $this->address_line_1,
			'address_line_2'         => $this->address_line_2,
			'address_line_3'         => $this->address_line_3,
			'administrative_level_1' => $this->administrative_level_1,
			'administrative_level_2' => $this->administrative_level_2,
			'country_code'           => $this->country_code,
			'postal_code'            => $this->postal_code,
			'coordinates'            => array(
				'lat'  => $this->lat,
				'long' => $this->long,
			),

			'formats'                => array(
				'long' => $this->get_full_address(),
			),
		);
	}
}
