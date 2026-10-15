<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

use WPDesk\CBFields\Collection\FieldSettings;

/**
 * Preserves the existing string value lifecycle for Free fields.
 */
final class ScalarFieldValueHandler implements FieldValueHandler {

	/**
	 * @return array<string, mixed>
	 */
	public function get_schema( FieldSettings $field_settings ): array {
		return [ 'type' => 'string' ];
	}

	/**
	 * @param mixed $raw_value
	 * @return mixed
	 */
	public function normalize( $raw_value, FieldSettings $field_settings ) {
		if ( $raw_value === null ) {
			$raw_value = '';
		}

		if ( ! is_scalar( $raw_value ) ) {
			return $raw_value;
		}

		$value = (string) $raw_value;
		if ( $field_settings->get_field_type() === 'textarea' ) {
			return sanitize_textarea_field( $value );
		}

		return sanitize_text_field( $value );
	}

	/**
	 * @param mixed $value
	 */
	public function validate( $value, FieldSettings $field_settings ): ValidationResult {
		if ( ! is_string( $value ) ) {
			return ValidationResult::invalid(
				'cffb_invalid_field_value',
				__( 'The checkout field value has an invalid format.', 'checkout-fields-for-blocks' )
			);
		}

		return ValidationResult::valid();
	}

	/**
	 * @param mixed $value
	 */
	public function has_value( $value, FieldSettings $field_settings ): bool {
		return is_string( $value ) && $value !== '';
	}

	/**
	 * @param mixed $checkout_value
	 * @return mixed
	 */
	public function prepare_order_value( $checkout_value, FieldSettings $field_settings, \WC_Order $order ) {
		return $checkout_value;
	}

	/**
	 * @param mixed $order_value
	 */
	public function format_for_display( $order_value, FieldSettings $field_settings, \WC_Order $order ): DisplayValue {
		if ( is_array( $order_value ) ) {
			$values = array_filter( $order_value, 'is_scalar' );
			return DisplayValue::text( implode( ', ', array_map( 'strval', $values ) ) );
		}

		if ( ! is_scalar( $order_value ) ) {
			return DisplayValue::text( '' );
		}

		$value = (string) $order_value;
		if ( $field_settings->has_options() ) {
			$label = $field_settings->get_option_label_by_value( $value );
			$value = $label !== '' ? $label : $value;
		}

		return DisplayValue::text( $value );
	}

	public function empty_value(): string {
		return '';
	}
}
