<?php

namespace WPDesk\CBFields\Hookable\Display;

use WPDesk\CBFields\Collection\FieldSettings;
use WPDesk\CBFields\Integration\FieldValueHandlerRegistry;

/**
 * Renders admin order editors for the core checkout field types.
 */
final class OrderAdminEditors {

	/**
	 * @var array<string, string>
	 */
	private const INPUT_TYPES = [
		'input-text'   => 'text',
		'input-email'  => 'email',
		'input-number' => 'number',
		'input-url'    => 'url',
	];

	public function register( FieldValueHandlerRegistry $registry ): void {
		foreach ( self::INPUT_TYPES as $field_type => $input_type ) {
			$registry->register_admin_editor(
				$field_type,
				fn ( FieldSettings $settings, $value ): string => $this->input( $settings, $value, $input_type )
			);
		}

		$registry->register_admin_editor( 'select-block', fn ( FieldSettings $settings, $value ): string => $this->select( $settings, $value ) );
		$registry->register_admin_editor( 'textarea', fn ( FieldSettings $settings, $value ): string => $this->textarea( $settings, $value ) );
		$registry->register_admin_editor( 'checkbox-input', fn ( FieldSettings $settings, $value ): string => $this->checkbox( $settings, $value ) );
	}

	private function field_name( FieldSettings $settings ): string {
		return 'cffb_order_fields[' . esc_attr( $settings->get_meta_name() ) . ']';
	}

	/**
	 * @param mixed $value
	 */
	private function input( FieldSettings $settings, $value, string $input_type ): string {
		return '<input type="' . esc_attr( $input_type ) . '" name="' . $this->field_name( $settings ) . '" value="' . esc_attr( is_scalar( $value ) ? (string) $value : '' ) . '">';
	}

	/**
	 * @param mixed $value
	 */
	private function textarea( FieldSettings $settings, $value ): string {
		return '<textarea name="' . $this->field_name( $settings ) . '">' . esc_textarea( is_scalar( $value ) ? (string) $value : '' ) . '</textarea>';
	}

	/**
	 * @param mixed $value
	 */
	private function checkbox( FieldSettings $settings, $value ): string {
		$name          = $this->field_name( $settings );
		$stored        = is_scalar( $value ) ? (string) $value : '';
		$checked_value = $stored !== '' ? $stored : $settings->get_default_value();

		return '<input type="hidden" name="' . $name . '" value=""><label><input type="checkbox" name="' . $name . '" value="' . esc_attr( $checked_value ) . '" ' . checked( $stored !== '', true, false ) . '> ' . esc_html__( 'Enabled', 'checkout-fields-for-blocks' ) . '</label>';
	}

	/**
	 * Keeps a stored value that is no longer a configured option selectable.
	 *
	 * @param mixed $value
	 */
	private function select( FieldSettings $settings, $value ): string {
		$options = $settings->get_options();
		if ( is_string( $value ) && $value !== '' && $settings->get_option_label_by_value( $value ) === '' ) {
			$options[] = [
				'label' => $value,
				'value' => $value,
			];
		}

		$html = '<select name="' . $this->field_name( $settings ) . '"><option value="">' . esc_html__( 'Select an option', 'checkout-fields-for-blocks' ) . '</option>';
		foreach ( $options as $option ) {
			$html .= '<option value="' . esc_attr( $option['value'] ) . '" ' . selected( $value, $option['value'], false ) . '>' . esc_html( $option['label'] ) . '</option>';
		}

		return $html . '</select>';
	}
}
