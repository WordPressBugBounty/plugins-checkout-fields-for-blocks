<?php

namespace WPDesk\CBFields\Data;

use WC_Order;
use WPDesk\CBFields\Collection\FieldSettings;
use WPDesk\CBFields\Exceptions\FieldMetaException;
use WPDesk\CBFields\Collection\FieldSettingsCollectionFactory;
use WPDesk\CBFields\Integration\FieldValueHandlerRegistry;

/**
 * Resolves fields meta data.
 */
class FieldsMetaResolver {

	/**
	 * @var FieldSettingsCollectionFactory
	 */
	private $settings_factory;

	private FieldValueHandlerRegistry $field_value_handler_registry;

	public function __construct(
		FieldSettingsCollectionFactory $field_settings_settings_factory,
		FieldValueHandlerRegistry $field_value_handler_registry
	) {
		$this->settings_factory             = $field_settings_settings_factory;
		$this->field_value_handler_registry = $field_value_handler_registry;
	}

	/**
	 * @param WC_Order $order
	 * @param string $page_id
	 *
	 * @return array<string, array{label: string, kind: string, value: string, display: string}>
	 */
	public function get_fields_meta( WC_Order $order, string $page_id ): array {
		return $this->settings_factory->get_collection()->reduce(
			function ( $data, FieldSettings $field_settings ) use ( $order, $page_id ) {
				if ( $field_settings->is_displayable( $page_id ) ) {
					try {
						$data[ $field_settings->get_meta_name() ] = $this->get_field_meta( $field_settings, $order );
					} catch ( FieldMetaException $e ) { // phpcs:ignore
						// ignore.
					}
				}

				return $data;
			},
			[]
		);
	}

	/**
	 * @param FieldSettings $field_settings
	 * @param WC_Order $order
	 *
	 * @throws FieldMetaException
	 * @return array{label: string, kind: string, value: string, display: string}
	 */
	private function get_field_meta( FieldSettings $field_settings, WC_Order $order ): array {
		if (
			! $this->field_value_handler_registry->supports(
				$field_settings->get_field_type(),
				$field_settings->get_block_name()
			)
		) {
			throw new FieldMetaException( 'Field type handler is not available.' );
		}

		$meta_name = $field_settings->get_meta_name();
		if ( '' === $meta_name ) {
			throw new FieldMetaException( 'Field meta name is not set.' );
		}

		$field_name = $field_settings->get_field_name();
		if ( '' === $field_name ) {
			throw new FieldMetaException( 'Field name is not set.' );
		}

		$handler       = $this->field_value_handler_registry->get( $field_settings->get_field_type() );
		$display_value = $handler->format_for_display( $order->get_meta( $meta_name ), $field_settings, $order );

		if ( $display_value->get_value() === '' ) {
			throw new FieldMetaException( 'Empty field value.' );
		}

		return [
			'label'   => $field_name,
			'kind'    => $display_value->get_kind(),
			'value'   => $display_value->get_value(),
			'display' => $display_value->get_label(),
		];
	}
}
