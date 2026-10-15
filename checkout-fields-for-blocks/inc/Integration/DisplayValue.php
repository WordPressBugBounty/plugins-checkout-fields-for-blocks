<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

/**
 * Describes a field value without allowing handlers to return arbitrary HTML.
 */
final class DisplayValue {

	public const KIND_TEXT = 'text';
	public const KIND_LINK = 'link';

	private string $kind;

	private string $value;

	private string $label;

	private function __construct( string $kind, string $value, string $label = '' ) {
		$this->kind  = $kind;
		$this->value = $value;
		$this->label = $label !== '' ? $label : $value;
	}

	public static function text( string $value ): self {
		return new self( self::KIND_TEXT, $value );
	}

	public static function link( string $value, string $label = '' ): self {
		return new self( self::KIND_LINK, $value, $label );
	}

	public function get_kind(): string {
		return $this->kind;
	}

	public function get_value(): string {
		return $this->value;
	}

	public function get_label(): string {
		return $this->label;
	}
}
