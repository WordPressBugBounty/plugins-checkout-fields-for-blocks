<?php

namespace WPDesk\CBFields\Hookable\Display;

use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use CBFieldsVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\CBFields\Collection\FieldSettings;
use WPDesk\CBFields\Collection\FieldSettingsCollectionFactory;
use WPDesk\CBFields\Integration\FieldValueHandlerRegistry;

/**
 * Displays and edits checkout fields on the admin order page.
 */
class OrderAdmin implements Hookable {

	const PAGE_ID = 'orderAdmin';

	private FieldSettingsCollectionFactory $settings_factory;

	private FieldValueHandlerRegistry $registry;

	private Renderer $renderer;

	public function __construct( FieldSettingsCollectionFactory $settings_factory, FieldValueHandlerRegistry $registry, Renderer $renderer ) {
		$this->settings_factory = $settings_factory;
		$this->registry         = $registry;
		$this->renderer         = $renderer;
	}

	public function hooks(): void {
		\add_action( 'woocommerce_admin_order_data_after_shipping_address', $this );
		\add_action( 'woocommerce_process_shop_order_meta', [ $this, 'save' ], 10, 2 );
		\add_action( 'post_edit_form_tag', [ $this, 'enable_legacy_file_uploads' ] );
		\add_action( 'order_edit_form_tag', [ $this, 'enable_hpos_file_uploads' ] );
	}

	/**
	 * @param mixed $post
	 * @internal
	 */
	public function enable_legacy_file_uploads( $post ): void {
		if ( $post instanceof \WP_Post && $post->post_type === 'shop_order' ) {
			$this->output_multipart_enctype();
		}
	}

	/**
	 * @param mixed $order
	 * @internal
	 */
	public function enable_hpos_file_uploads( $order ): void {
		if ( $order instanceof \WC_Order ) {
			$this->output_multipart_enctype();
		}
	}

	private function output_multipart_enctype(): void {
		echo ' enctype="multipart/form-data"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param \WC_Order $order
	 */
	public function __invoke( $order ): void {
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$fields       = [];
		$has_editable = false;
		foreach ( $this->settings_factory->get_collection() as $settings ) {
			if ( ! $settings->is_displayable( self::PAGE_ID ) || $settings->get_meta_name() === '' || $settings->get_field_name() === '' ) {
				continue;
			}

			$type = $settings->get_field_type();
			if ( ! $this->registry->supports( $type, $settings->get_block_name() ) ) {
				continue;
			}

			$meta_name = $settings->get_meta_name();
			$value     = $order->get_meta( $meta_name );
			$editable  = $this->has_editor( $settings );
			$display   = $this->registry->get( $type )->format_for_display( $value, $settings, $order );
			$fields[]  = [
				'label'   => $settings->get_field_name(),
				'key'     => $meta_name,
				'edit'    => $editable,
				'html'    => $editable ? $this->registry->render_admin_editor( $type, $settings, $value, $order ) : '',
				'kind'    => $display->get_kind(),
				'display' => $display->get_label(),
				'url'     => $display->get_value(),
			];

			$has_editable = $has_editable || $editable;
		}

		if ( $fields === [] ) {
			return;
		}

		$this->renderer->output_render(
			'order-admin-fields',
			[
				'fields'              => $fields,
				'has_editable_fields' => $has_editable,
			]
		);
	}

	/**
	 * @param int             $order_id
	 * @param \WC_Order|null $order
	 * @internal
	 */
	public function save( $order_id, $order = null ): void {
		if ( ! is_admin() || ! isset( $_POST['cffb_order_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cffb_order_fields_nonce'] ) ), 'cffb_save_order_fields' ) ) {
			return;
		}

		$order_id = absint( $order_id );
		if ( ! current_user_can( 'edit_shop_order', $order_id ) || ( isset( $_POST['cffb_order_fields_editing'] ) ? sanitize_text_field( wp_unslash( $_POST['cffb_order_fields_editing'] ) ) : '' ) !== '1' || ! isset( $_POST['cffb_rendered_fields'] ) || ! is_array( $_POST['cffb_rendered_fields'] ) ) {
			return;
		}

		$order = $order instanceof \WC_Order ? $order : wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		// Field handlers normalize and validate values; sanitizing here would strip textarea newlines.
		$submitted       = isset( $_POST['cffb_order_fields'] ) && is_array( $_POST['cffb_order_fields'] ) ? wp_unslash( $_POST['cffb_order_fields'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$rendered_values = array_filter( map_deep( wp_unslash( $_POST['cffb_rendered_fields'] ), 'sanitize_text_field' ), 'is_string' );
		$rendered        = array_map( 'sanitize_text_field', $rendered_values );
		[ 'errors' => $errors, 'after_save' => $after_save, 'changed' => $changed ] = $this->save_fields( $order, $submitted, $rendered );

		if ( $changed ) {
			$saved = false;
			try {
				$order->save();
				$saved = true;
			} finally {
				foreach ( $after_save as $callback ) {
					$callback( $saved );
				}
			}
		}
		foreach ( array_unique( $errors ) as $error ) {
			\WC_Admin_Meta_Boxes::add_error( $error );
		}
	}

	/**
	 * @param array<string, mixed> $submitted
	 * @param string[]             $rendered
	 * @return array{errors: string[], after_save: callable[], changed: bool}
	 */
	private function save_fields( \WC_Order $order, array $submitted, array $rendered ): array {
		$result = [
			'errors'     => [],
			'after_save' => [],
			'changed'    => false,
		];
		foreach ( $this->settings_factory->get_collection() as $settings ) {
			if ( ! $this->is_editable_field( $settings, $rendered ) ) {
				continue;
			}

			$key   = $settings->get_meta_name();
			$type  = $settings->get_field_type();
			$value = $this->registry->has_admin_saver( $type )
				? $this->save_custom_admin_field( $type, $settings, $key, $submitted, $order )
				: $this->save_regular_admin_field( $type, $settings, $key, $submitted, $order );
			if ( is_string( $value ) ) {
				$result['errors'][] = $value;
			} elseif ( is_callable( $value ) ) {
				$result['after_save'][] = $value;
				$result['changed']      = true;
			} elseif ( $value ) {
				$result['changed'] = true;
			}
		}
		return $result;
	}

	/**
	 * @param array<string, mixed> $submitted
	 * @return callable|string|null
	 */
	private function save_custom_admin_field( string $type, FieldSettings $settings, string $key, array $submitted, \WC_Order $order ) {
		$file = $this->get_admin_upload( $key );
		if ( $file !== null && ! current_user_can( 'upload_files' ) ) {
			return __( 'You are not allowed to upload files.', 'checkout-fields-for-blocks' );
		}
		$raw = array_key_exists( $key, $submitted ) ? $submitted[ $key ] : null;
		return $this->registry->save_admin_value( $type, $settings, $raw, $file, $order );
	}

	/**
	 * @param array<string, mixed> $submitted
	 * @return string|bool
	 */
	private function save_regular_admin_field( string $type, FieldSettings $settings, string $key, array $submitted, \WC_Order $order ) {
		$handler   = $this->registry->get( $type );
		$old_value = $order->get_meta( $key );
		$raw       = array_key_exists( $key, $submitted ) ? $submitted[ $key ] : $handler->empty_value();
		$value     = $handler->normalize( $raw, $settings );
		if ( $value === $old_value || ( $value === [] && $old_value === '' ) || ( is_array( $value ) && is_array( $old_value ) && $this->same_values( $value, $old_value ) ) ) {
			return false;
		}
		$validation = $handler->validate( $value, $settings );
		if ( ! $validation->is_valid() ) {
			return $validation->get_message();
		}
		$order->update_meta_data( $key, $handler->prepare_order_value( $value, $settings, $order ) );
		return true;
	}

	/**
	 * @param string[] $rendered
	 */
	private function is_editable_field( FieldSettings $settings, array $rendered ): bool {
		$key = $settings->get_meta_name();

		return $settings->is_displayable( self::PAGE_ID )
			&& $key !== ''
			&& in_array( $key, $rendered, true )
			&& $this->has_editor( $settings );
	}

	private function has_editor( FieldSettings $settings ): bool {
		$type = $settings->get_field_type();

		return $this->registry->supports( $type, $settings->get_block_name() ) && $this->registry->has_admin_editor( $type );
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private function get_admin_upload( string $meta_name ): ?array {
		if ( ! isset( $_FILES['cffb_order_files'] ) || ! is_array( $_FILES['cffb_order_files'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- save() verifies the nonce before this helper runs; file data is validated by the registered upload handler.
			return null;
		}

		$files = $_FILES['cffb_order_files']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- save() verifies the nonce before this helper runs; file data is validated by the registered upload handler.
		$file  = [];
		foreach ( [ 'name', 'tmp_name', 'error', 'size' ] as $key ) {
			if ( ! isset( $files[ $key ] ) || ! is_array( $files[ $key ] ) || ! array_key_exists( $meta_name, $files[ $key ] ) ) {
				return null;
			}
			$file[ $key ] = $files[ $key ][ $meta_name ];
		}

		return (int) $file['error'] === UPLOAD_ERR_NO_FILE ? null : $file;
	}

	/**
	 * Treat multiselect order as irrelevant when deciding whether an existing value changed.
	 *
	 * @param array<int, mixed> $first
	 * @param array<int, mixed> $second
	 */
	private function same_values( array $first, array $second ): bool {
		sort( $first );
		sort( $second );
		return $first === $second;
	}
}
