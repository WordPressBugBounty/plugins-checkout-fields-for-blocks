<?php

declare( strict_types=1 );

namespace WPDesk\CBFields\Hookable\Registrator;

use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\CBFields\Integration\PluginBridge;

/**
 * Registers the public editor and checkout runtimes used by add-ons.
 */
final class PublicRuntime implements Hookable {

	private const RUNTIME_PATH = 'build/js/runtime';

	private string $plugin_dir;

	public function __construct( string $plugin_dir ) {
		$this->plugin_dir = $plugin_dir;
	}

	public function hooks(): void {
		add_action( 'init', [ $this, 'register_scripts' ] );
	}

	/**
	 * @internal
	 */
	public function register_scripts(): void {
		$this->register_script( 'editor', PluginBridge::EDITOR_RUNTIME_SCRIPT_HANDLE );
		$this->register_script( 'checkout', PluginBridge::CHECKOUT_RUNTIME_SCRIPT_HANDLE );
	}

	private function register_script( string $script_name, string $script_handle ): void {
		$asset_path = $this->plugin_dir . '/' . self::RUNTIME_PATH . '/' . $script_name . '.asset.php';
		$asset      = file_exists( $asset_path )
			? require $asset_path
			: [
				'dependencies' => [],
				'version'      => '1.0.0',
			];

		wp_register_script(
			$script_handle,
			plugins_url( self::RUNTIME_PATH . '/' . $script_name . '.js', $this->plugin_dir . '/checkout-fields-for-blocks.php' ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations( $script_handle, 'checkout-fields-for-blocks', $this->plugin_dir . '/lang/' );
	}
}
