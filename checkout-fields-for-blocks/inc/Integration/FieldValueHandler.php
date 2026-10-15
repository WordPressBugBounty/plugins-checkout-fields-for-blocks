<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

use WPDesk\CBFields\Collection\FieldSettings;

/**
 * Defines the value lifecycle owned by one checkout field type.
 */
interface FieldValueHandler {

	/**
	 * @return array<string, mixed>
	 **/
	public function get_schema( FieldSettings $field_settings ): array;

	/**
	 * @param mixed $raw_value
	 * @return mixed
	 */
	public function normalize( $raw_value, FieldSettings $field_settings );

	/**
	 * @param mixed $value
	 */
	public function validate( $value, FieldSettings $field_settings ): ValidationResult;

	/**
	 * Whether the value represents a completed field value.
	 *
	 * @param mixed $value
	 */
	public function has_value( $value, FieldSettings $field_settings ): bool;

	/**
	 * @param mixed $checkout_value
	 * @return mixed
	 */
	public function prepare_order_value( $checkout_value, FieldSettings $field_settings, \WC_Order $order );

	/**
	 * @param mixed $order_value
	 */
	public function format_for_display( $order_value, FieldSettings $field_settings, \WC_Order $order ): DisplayValue;

	/**
	 * @return mixed
	 */
	public function empty_value();
}
