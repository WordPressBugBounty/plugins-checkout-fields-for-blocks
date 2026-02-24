<?php

namespace WPDesk\CBFields\Integration;

use WPDesk\CBFields\Collection\FieldSettingsCollection;

/**
 * Exposes integration points for extensions like the PRO plugin.
 *
 * This class provides a clean interface for extensions to access
 * core plugin services and ensures compatibility between versions.
 */
class PluginBridge {

	private string $version;

	private string $dev_version;

	private FieldSettingsCollection $field_settings_collection;

	public function __construct(
		string $version,
		string $dev_version,
		FieldSettingsCollection $field_settings_collection
	) {
		$this->version                   = $version;
		$this->dev_version               = $dev_version;
		$this->field_settings_collection = $field_settings_collection;
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
}
