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

	public function get_editor_runtime_script_handle(): string {
		return self::EDITOR_RUNTIME_SCRIPT_HANDLE;
	}

	public function get_checkout_runtime_script_handle(): string {
		return self::CHECKOUT_RUNTIME_SCRIPT_HANDLE;
	}
}
