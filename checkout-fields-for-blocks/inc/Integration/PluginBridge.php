<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

use WPDesk\CBFields\Collection\FieldSettingsCollection;

/**
 * Exposes integration points for extensions like the PRO plugin.
 *
 * This class provides a clean interface for extensions to access
 * core plugin services and ensures compatibility between versions.
 */
class PluginBridge {

	public const EDITOR_RUNTIME_SCRIPT_HANDLE   = 'checkout-fields-for-blocks-editor-runtime';
	public const CHECKOUT_RUNTIME_SCRIPT_HANDLE = 'checkout-fields-for-blocks-checkout-runtime';

	private string $version;

	private string $dev_version;

	private FieldSettingsCollection $field_settings_collection;

	private ?FieldValueHandlerRegistry $field_value_handler_registry;

	public function __construct(
		string $version,
		string $dev_version,
		FieldSettingsCollection $field_settings_collection,
		?FieldValueHandlerRegistry $field_value_handler_registry = null
	) {
		$this->version                      = $version;
		$this->dev_version                  = $dev_version;
		$this->field_settings_collection    = $field_settings_collection;
		$this->field_value_handler_registry = $field_value_handler_registry;
	}

	/**
	 * Get the free plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	public function get_dev_version(): string {
		return $this->dev_version;
	}

	public function get_field_settings_collection(): FieldSettingsCollection {
		return $this->field_settings_collection;
	}

	public function get_field_value_handler_registry(): ?FieldValueHandlerRegistry {
		return $this->field_value_handler_registry;
	}

	/**
	 * Register an optional admin editor for a field type.
	 *
	 * @param callable $editor Receives FieldSettings, the current order value, and optionally the WC_Order; returns editor HTML.
	 */
	public function register_admin_editor( string $field_type, callable $editor ): bool {
		if ( ! $this->field_value_handler_registry || ! $this->field_value_handler_registry->has( $field_type ) ) {
			return false;
		}

		$this->field_value_handler_registry->register_admin_editor( $field_type, $editor );
		return true;
	}

	/**
	 * Register an optional admin saver for a field type.
	 *
	 * @param callable $saver Receives FieldSettings, submitted value, uploaded file, and order; returns an error, after-save callback, or null.
	 */
	public function register_admin_saver( string $field_type, callable $saver ): bool {
		if ( ! $this->field_value_handler_registry || ! $this->field_value_handler_registry->has( $field_type ) ) {
			return false;
		}

		$this->field_value_handler_registry->register_admin_saver( $field_type, $saver );
		return true;
	}

	public function get_editor_runtime_script_handle(): string {
		return self::EDITOR_RUNTIME_SCRIPT_HANDLE;
	}

	public function get_checkout_runtime_script_handle(): string {
		return self::CHECKOUT_RUNTIME_SCRIPT_HANDLE;
	}
}
