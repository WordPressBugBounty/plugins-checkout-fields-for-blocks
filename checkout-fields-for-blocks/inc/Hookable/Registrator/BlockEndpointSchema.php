<?php

namespace WPDesk\CBFields\Hookable\Registrator;

use Automattic\WooCommerce\StoreApi\StoreApi;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use WPDesk\CBFields\Collection\FieldSettingsCollectionFactory;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use WPDesk\CBFields\Collection\FieldSettings;
use WPDesk\CBFields\Integration\FieldValueHandlerRegistry;

/**
 * Extends store API with endpoint schema for our blocks.
 */
class BlockEndpointSchema implements Hookable {

	/**
	 * @var string
	 */
	private $namespace;

	/**
	 * @var FieldSettingsCollectionFactory
	 */
	private $settings_factory;

	private FieldValueHandlerRegistry $field_value_handler_registry;

	public function __construct(
		FieldSettingsCollectionFactory $settings_factory,
		string $plugin_namespace,
		FieldValueHandlerRegistry $field_value_handler_registry
	) {
		$this->settings_factory             = $settings_factory;
		$this->namespace                    = $plugin_namespace;
		$this->field_value_handler_registry = $field_value_handler_registry;
	}

	public function hooks() {
		\add_action( 'woocommerce_blocks_loaded', [ $this, 'extend_store_api' ] );
	}

	public function extend_store_api(): void {
		// @phpstan-ignore-next-line
		$store_api = StoreApi::container()->get( ExtendSchema::class );

		if ( ! is_object( $store_api ) || ! is_callable( [ $store_api, 'register_endpoint_data' ] ) ) {
			return;
		}

		$store_api->register_endpoint_data(
			[
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => $this->namespace,
				'schema_callback' => [ $this, 'register_endpoint_data' ],
				'schema_type'     => ARRAY_A,
			]
		);
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function register_endpoint_data(): array {
		return $this->settings_factory->get_collection()->reduce(
			function ( $data, FieldSettings $field_settings ) {
				$meta_name = $field_settings->get_meta_name();
				$label     = $field_settings->get_label();
				if (
					$meta_name !== '' &&
					$this->field_value_handler_registry->supports(
						$field_settings->get_field_type(),
						$field_settings->get_block_name()
					)
				) {
					$handler            = $this->field_value_handler_registry->get( $field_settings->get_field_type() );
					$data[ $meta_name ] = array_merge(
						$handler->get_schema( $field_settings ),
						[
							'description' => $label !== '' ? $label : __( 'Custom checkout block field', 'checkout-fields-for-blocks' ),
							'context'     => [ 'view', 'edit' ],
							'readonly'    => true,
							'optional'    => ! $field_settings->is_required(),
						]
					);
				}
				return $data;
			},
			[]
		);
	}
}
