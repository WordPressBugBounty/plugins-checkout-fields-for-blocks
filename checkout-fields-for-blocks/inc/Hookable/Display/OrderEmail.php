<?php

namespace WPDesk\CBFields\Hookable\Display;

use WPDesk\CBFields\Data\FieldsMetaResolver;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Display fields on order email templates.
 */
class OrderEmail implements Hookable {

	/**
	 * @var FieldsMetaResolver
	 */
	private $meta_resolver;

	private const PAGE_ID = 'orderEmail';

	public function __construct( FieldsMetaResolver $meta_resolver ) {
		$this->meta_resolver = $meta_resolver;
	}

	public function hooks(): void {
		\add_filter( 'woocommerce_email_order_meta_fields', $this, 10, 3 );
	}

	/**
	 * Add order meta to email templates (and display it the woocommerce way).
	 *
	 * @param array<string, array{label: string, value: string}> $fields
	 * @param bool $sent_to_admin If should sent to admin.
	 * @param mixed $order Order instance.
	 *
	 * @return array<string, array{label: string, value: string}>
	 */
	public function __invoke( $fields, $sent_to_admin, $order ): array {
		if ( ! $order instanceof \WC_Order ) {
			return $fields;
		}

		$additional_fields = $this->meta_resolver->get_fields_meta( $order, self::PAGE_ID );
		if ( empty( $additional_fields ) ) {
			return $fields;
		}

		$additional_fields = array_map(
			function ( $field ) {
				return [
					'label' => $field['label'],
					'value' => '<br />' . nl2br( $field['value'] ),
				];
			},
			$additional_fields
		);

		return array_merge( $fields, $additional_fields );
	}
}
