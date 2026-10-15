<?php

namespace WPDesk\CBFields\Hookable;

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\CBFields\Collection\FieldSettingsCollectionFactory;
use WPDesk\CBFields\Integration\FieldValueHandlerRegistry;

/**
 * Saves custom checkout fields data to order meta.
 */
class OrderSaver implements Hookable {

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
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', $this, 10, 2 );
	}

	/**
	 * @param \WC_Order $order
	 * @param mixed $request
	 */
	public function __invoke( $order, $request ): void {
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		if ( ! $request instanceof \WP_REST_Request ) {
			return;
		}

		if ( ! isset( $request['extensions'][ $this->namespace ] ) ) {
			return;
		}

		$raw_extension_data = $request->get_param( 'extensions' )[ $this->namespace ];
		if ( ! is_array( $raw_extension_data ) ) {
			return;
		}

		$settings = $this->settings_factory->get_collection();

		foreach ( $settings as $field_settings ) {
			$meta_name = $field_settings->get_meta_name();
			if ( $meta_name === '' ) {
				continue;
			}

			$field_type = $field_settings->get_field_type();
			if ( ! $this->field_value_handler_registry->supports( $field_type, $field_settings->get_block_name() ) ) {
				continue;
			}

			$handler        = $this->field_value_handler_registry->get( $field_type );
			$raw_value      = $raw_extension_data[ $meta_name ] ?? $handler->empty_value();
			$checkout_value = $handler->normalize( $raw_value, $field_settings );
			$validation     = $handler->validate( $checkout_value, $field_settings );

			if ( ! $validation->is_valid() ) {
				throw new RouteException( $validation->get_error_code(), $validation->get_message(), 400 );
			}

			$order_value = $handler->prepare_order_value( $checkout_value, $field_settings, $order );
			$order->update_meta_data( $meta_name, $order_value );
		}

		$order->save();
	}
}
