<?php

namespace WPDesk\CBFields\Hookable;

use WPDesk\CBFields\Integration\PluginBridge;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Hook point for extensions to access
 */
class Integration implements Hookable {

	private PluginBridge $plugin_bridge;

	public function __construct( PluginBridge $plugin_bridge ) {
		$this->plugin_bridge = $plugin_bridge;
	}

	public function hooks(): void {
		add_action( 'woocommerce_init', $this );
	}

	public function __invoke(): void {
		do_action(
			'checkout_fields_blocks_ready',
			$this->plugin_bridge
		);
	}
}
