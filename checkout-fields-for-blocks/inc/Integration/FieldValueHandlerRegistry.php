<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

use CBFieldsVendor\Psr\Log\LoggerInterface;
use WPDesk\CBFields\Collection\FieldSettings;

/**
 * Owns field-type handlers and their explicit Gutenberg block identities.
 */
final class FieldValueHandlerRegistry {

	private FieldValueHandler $fallback_handler;

	private LoggerInterface $logger;

	/**
	 * @var array<string, FieldValueHandler>
	 */
	private array $handlers = [];

	/**
	 * @var array<string, string>
	 */
	private array $block_types = [];

	public function __construct( FieldValueHandler $fallback_handler, LoggerInterface $logger ) {
		$this->fallback_handler = $fallback_handler;
		$this->logger           = $logger;
	}

	public function register( string $field_type, string $block_name, FieldValueHandler $handler ): bool {
		if ( ! $this->is_valid_field_type( $field_type ) || ! $this->is_valid_block_name( $block_name ) ) {
			$this->logger->warning(
				'Rejected malformed checkout field type registration.',
				[
					'field_type' => $field_type,
					'block_name' => $block_name,
				]
			);
			return false;
		}

		if ( isset( $this->handlers[ $field_type ] ) || isset( $this->block_types[ $block_name ] ) ) {
			$this->logger->warning(
				'Rejected duplicate checkout field type registration.',
				[
					'field_type' => $field_type,
					'block_name' => $block_name,
				]
			);
			return false;
		}

		$this->handlers[ $field_type ]    = $handler;
		$this->block_types[ $block_name ] = $field_type;

		return true;
	}

	public function has( string $field_type ): bool {
		return isset( $this->handlers[ $field_type ] );
	}

	public function get( string $field_type ): FieldValueHandler {
		return $this->handlers[ $field_type ] ?? $this->fallback_handler;
	}

	/**
	 * @param mixed $value
	 */
	public function has_value( string $field_type, $value, FieldSettings $field_settings ): bool {
		return $this->get( $field_type )->has_value( $value, $field_settings );
	}

	public function get_field_type_by_block_name( string $block_name ): ?string {
		return $this->block_types[ $block_name ] ?? null;
	}

	public function supports( string $field_type, string $block_name ): bool {
		if ( ! $this->has( $field_type ) ) {
			return false;
		}

		// Older saved settings may not include a block name.
		if ( $block_name === '' ) {
			return true;
		}

		return ( $this->block_types[ $block_name ] ?? null ) === $field_type;
	}

	/**
	 * @return string[]
	 */
	public function get_registered_field_types(): array {
		return array_keys( $this->handlers );
	}

	private function is_valid_field_type( string $field_type ): bool {
		return preg_match( '/^[a-z0-9][a-z0-9_-]*$/', $field_type ) === 1;
	}

	private function is_valid_block_name( string $block_name ): bool {
		return preg_match( '/^[a-z0-9][a-z0-9-]*\/[a-z0-9][a-z0-9-]*$/', $block_name ) === 1;
	}
}
