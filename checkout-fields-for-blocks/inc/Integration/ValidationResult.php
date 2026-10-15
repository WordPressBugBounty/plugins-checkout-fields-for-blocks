<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Integration;

/**
 * Represents the expected outcome of validating a checkout field value.
 */
final class ValidationResult {

	private bool $valid;

	private string $error_code;

	private string $message;

	private function __construct( bool $valid, string $error_code, string $message ) {
		$this->valid      = $valid;
		$this->error_code = $error_code;
		$this->message    = $message;
	}

	public static function valid(): self {
		return new self( true, '', '' );
	}

	public static function invalid( string $error_code, string $message ): self {
		return new self( false, $error_code, $message );
	}

	public function is_valid(): bool {
		return $this->valid;
	}

	public function get_error_code(): string {
		return $this->error_code;
	}

	public function get_message(): string {
		return $this->message;
	}
}
