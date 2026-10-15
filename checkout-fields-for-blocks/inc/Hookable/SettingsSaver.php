<?php

namespace WPDesk\CBFields\Hookable;

use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\CBFields\Collection\FieldSettingsCollectionFactory;
use WPDesk\CBFields\Integration\BlockFieldSettingsExtractor;

/**
 * Saves block settings in the options table.
 */
class SettingsSaver implements Hookable {

	/**
	 * @var FieldSettingsCollectionFactory
	 */
	private $field_settings_collection_factory;

	private BlockFieldSettingsExtractor $field_settings_extractor;

	public function __construct(
		FieldSettingsCollectionFactory $field_settings_collection_factory,
		BlockFieldSettingsExtractor $field_settings_extractor
	) {
		$this->field_settings_collection_factory = $field_settings_collection_factory;
		$this->field_settings_extractor          = $field_settings_extractor;
	}

	public function hooks() {
		add_action( 'save_post', $this );
	}

	/**
	 * Save block settings in the options table.
	 *
	 * @param int $post_id The ID of the post being saved.
	 * @return void
	 */
	public function __invoke( $post_id ) {
		if ( \wc_get_page_id( 'checkout' ) !== $post_id ) {
			return;
		}

		$post_content = \get_post_field( 'post_content', $post_id );
		if ( ! is_string( $post_content ) ) {
			return;
		}

		$blocks     = \parse_blocks( $post_content );
		$field_data = $this->field_settings_extractor->extract( $blocks );

		$field_settings_collection = $this->field_settings_collection_factory->get_collection();
		$field_settings_collection->set_raw_settings( $field_data );
		$field_settings_collection->update();
	}
}
