<?php
/**
 * Contains the Validator class.
 *
 * @package wp-objective
 */

namespace Wrd\WpObjective\Support;

use WP_Error;

/**
 * Validates orders.
 */
class Validator {
	/**
	 * The property schema.
	 *
	 * @var array
	 */
	private $property_schema;

	/**
	 * Create a validator.
	 *
	 * @param array $property_schema The property schema.
	 */
	public function __construct( array $property_schema ) {
		$this->property_schema = $property_schema;
	}

	/**
	 * Get the property schema.
	 *
	 * @return array
	 */
	public function get_property_schema(): array {
		return $this->property_schema;
	}

	/**
	 * Check if a set of fields are valid.
	 *
	 * @param array $values Array of values to validate.
	 *
	 * @return WP_Error|true
	 */
	public function validate( array $values ): WP_Error|true {
		$required = array();

		foreach ( $this->property_schema as $key => $arg ) {
			$is_required = isset( $arg['required'] ) && true === $arg['required'];

			if ( $is_required && ! isset( $values[ $key ] ) || null === $values[ $key ] ) {
				$required[] = $key;
			}
		}

		if ( ! empty( $required ) ) {
			return new WP_Error(
				'rest_missing_callback_param',
				/* translators: %s: List of required parameters. */
				sprintf( __( 'Missing parameter(s): %s' ), implode( ', ', $required ) ),
				array(
					'status' => 400,
					'params' => $required,
				)
			);
		}

		$invalid_params  = array();
		$invalid_details = array();

		foreach ( $this->property_schema as $key => $arg ) {
			$value = isset( $values[ $key ] ) ? $values[ $key ] : null;

			$schema_valid = rest_validate_value_from_schema( $value, $arg, $key );

			if ( is_wp_error( $schema_valid ) ) {
				$invalid_details[ $key ] = implode( ' ', $schema_valid->get_error_messages() );
				$invalid_details[ $key ] = rest_convert_error_to_response( $schema_valid )->get_data();

				continue;
			}

			if ( null !== $value && ! empty( $arg['validate_callback'] ) ) {
				/**
				 * The validatity from the callback.
				 *
				 * @var bool|\WP_Error $valid_check
				 */
				$valid_check = call_user_func( $arg['validate_callback'], $value, $values, $key );

				if ( false === $valid_check ) {
					$invalid_params[ $key ] = __( 'Invalid parameter.' );
				}

				if ( is_wp_error( $valid_check ) ) {
					$invalid_params[ $key ]  = implode( ' ', $valid_check->get_error_messages() );
					$invalid_details[ $key ] = rest_convert_error_to_response( $valid_check )->get_data();
				}
			}
		}

		if ( $invalid_params ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: List of invalid parameters. */
				sprintf( __( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
				array(
					'status'  => 400,
					'params'  => $invalid_params,
					'details' => $invalid_details,
				)
			);
		}

		return true;
	}

	/**
	 * Sanitize a set of values.
	 *
	 * @param array $values Array of values to sanitize.
	 *
	 * @return WP_Error|array
	 */
	public function sanitize( array $values ): WP_Error|array {
		$sanitized_values = array();

		$invalid_params  = array();
		$invalid_details = array();

		foreach ( $values as $key => $value ) {
			if ( ! isset( $this->property_schema[ $key ] ) ) {
				// Unknown parameter, skip it.
				continue;
			}

			$param_args = $this->property_schema[ $key ];

			// If the arg has a type but no sanitize_callback attribute, default to rest_sanitize_value_from_schema.
			if ( ! array_key_exists( 'sanitize_callback', $param_args ) && ! empty( $param_args['type'] ) ) {
				$sanitized_value = rest_sanitize_value_from_schema( $value, $param_args, $key );

				if ( is_wp_error( $sanitized_value ) ) {
					$invalid_params[ $key ]  = implode( ' ', $sanitized_value->get_error_messages() );
					$invalid_details[ $key ] = rest_convert_error_to_response( $sanitized_value )->get_data();
				} else {
					$sanitized_values[ $key ] = $sanitized_value;
				}
				continue;
			}

			// If there's no sanitize_callback, nothing to do here.
			if ( empty( $param_args['sanitize_callback'] ) ) {
				$sanitized_values[ $key ] = $value;
				continue;
			}

			/**
			 * Sanitized value.
			 *
			 * @var mixed|WP_Error $sanitized_value
			 */
			$sanitized_value = call_user_func( $param_args['sanitize_callback'], $value, $values, $key );

			if ( is_wp_error( $sanitized_value ) ) {
				$invalid_params[ $key ]  = implode( ' ', $sanitized_value->get_error_messages() );
				$invalid_details[ $key ] = rest_convert_error_to_response( $sanitized_value )->get_data();
			} else {
				$sanitized_values[ $key ] = $sanitized_value;
			}
		}

		if ( $invalid_params ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: List of invalid parameters. */
				sprintf( __( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
				array(
					'status'  => 400,
					'params'  => $invalid_params,
					'details' => $invalid_details,
				)
			);
		}

		return $sanitized_values;
	}
}
