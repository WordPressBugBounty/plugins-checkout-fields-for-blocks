<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

/**
 * Extracts registered checkout field settings from parsed Gutenberg blocks.
 */
final class BlockFieldSettingsExtractor {

	private FieldValueHandlerRegistry $field_value_handler_registry;

	public function __construct( FieldValueHandlerRegistry $field_value_handler_registry ) {
		$this->field_value_handler_registry = $field_value_handler_registry;
	}

	/**
	 * @param array<array<string, mixed>> $blocks
	 * @return array<string, array<string, mixed>>
	 */
	public function extract( array $blocks ): array {
		$field_data = [];

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			$field = $this->extract_field( $block );
			if ( $field !== null ) {
				$field_data[ $field['meta_name'] ] = $field['attributes'];
			}

			$inner_blocks = $block['innerBlocks'] ?? [];
			if ( is_array( $inner_blocks ) && $inner_blocks !== [] ) {
				$field_data = array_merge( $field_data, $this->extract( $inner_blocks ) );
			}
		}

		return $field_data;
	}

	/**
	 * @param array<string, mixed> $block
	 * @return array{meta_name: string, attributes: array<string, mixed>}|null
	 */
	private function extract_field( array $block ): ?array {
		$attributes = $block['attrs'] ?? [];
		$block_name = $block['blockName'] ?? '';

		if ( ! is_array( $attributes ) || ! is_string( $block_name ) ) {
			return null;
		}

		$meta_name  = $attributes['metaName'] ?? '';
		$field_type = $this->field_value_handler_registry->get_field_type_by_block_name( $block_name );

		if ( ! is_string( $meta_name ) || $meta_name === '' || $field_type === null ) {
			return null;
		}

		$attributes['type']      = $field_type;
		$attributes['blockName'] = $block_name;

		return [
			'meta_name'  => $meta_name,
			'attributes' => $attributes,
		];
	}
}
