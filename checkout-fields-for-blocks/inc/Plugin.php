<?php
/**
 * Plugin main class.
 */

namespace WPDesk\CBFields;

use CBFieldsVendor\Psr\Log\LoggerAwareTrait;
use CBFieldsVendor\Psr\Log\LoggerAwareInterface;
use WPDesk\CBFields\Data\FieldsMetaResolver;
use CBFieldsVendor\WPDesk\View\Renderer\Renderer;
use CBFieldsVendor\WPDesk\View\Resolver\DirResolver;
use CBFieldsVendor\WPDesk\View\Resolver\ChainResolver;
use CBFieldsVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use WPDesk\CBFields\Collection\FieldSettingsCollectionFactory;
use CBFieldsVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use WPDesk\CBFields\Integration\PluginBridge;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @codeCoverageIgnore
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {

	use LoggerAwareTrait;
	use HookableParent;

	private const NAMESPACE = 'checkout-fields-for-blocks';

	private string $dev_version = '2';

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function hooks(): void {
		parent::hooks();

		$renderer                          = $this->get_renderer();
		$field_settings_collection_factory = new FieldSettingsCollectionFactory();
		$field_meta_resolver               = new FieldsMetaResolver( $field_settings_collection_factory );

		// Registrators.
		$this->add_hookable( new Hookable\Registrator\BlockNamespace( self::NAMESPACE ) );
		$this->add_hookable( new Hookable\Registrator\BlockCategories( self::NAMESPACE ) );
		$this->add_hookable( new Hookable\Registrator\Blocks( $this->plugin_info->get_plugin_dir() ) );
		$this->add_hookable( new Hookable\Registrator\BlockEndpointSchema( $field_settings_collection_factory, self::NAMESPACE ) );
		// Frontend.
		$this->add_hookable( new Hookable\OrderSaver( $field_settings_collection_factory, self::NAMESPACE ) );
		// Backend.
		$this->add_hookable( new Hookable\SettingsSaver( $field_settings_collection_factory ) );
		// Display.
		$this->add_hookable( new Hookable\Display\OrderEmail( $field_meta_resolver ) );
		$this->add_hookable( new Hookable\Display\OrderAdmin( $field_meta_resolver, $renderer ) );
		$this->add_hookable( new Hookable\Display\OrderConfirmation( $field_meta_resolver, $renderer ) );
		$this->add_hookable( new Hookable\Display\OrderMyAccount( $field_meta_resolver, $renderer ) );
		// Hook point for integrations.
		$this->add_hookable(
			new Hookable\Integration(
				new PluginBridge(
					$this->plugin_info->get_version(),
					$this->dev_version,
					$field_settings_collection_factory->get_collection()
				)
			)
		);

		$this->hooks_on_hookable_objects();
	}

	/**
	 * Init renderer.
	 */
	private function get_renderer(): Renderer {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new DirResolver( $this->plugin_info->get_plugin_dir() . '/templates/' ) );
		return new SimplePhpRenderer( $resolver );
	}

	public function admin_enqueue_scripts(): void {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( $screen->post_type !== 'shop_order' ) {
			return;
		}

		$asset_file = include $this->plugin_info->get_plugin_dir() . '/build/css/admin.asset.php';

		wp_enqueue_style(
			'checkout-fields-for-blocks-admin',
			plugins_url( 'build/css/admin.css', $this->plugin_info->get_plugin_dir() . '/checkout-fields-for-blocks.php' ),
			$asset_file['dependencies'],
			$asset_file['version']
		);
	}

	public function wp_enqueue_scripts(): void {
		if ( ! is_order_received_page() && ! is_view_order_page() ) {
			return;
		}

		$asset_file = include $this->plugin_info->get_plugin_dir() . '/build/css/frontend.asset.php';

		wp_enqueue_style(
			'checkout-fields-for-blocks-frontend',
			plugins_url( 'build/css/frontend.css', $this->plugin_info->get_plugin_dir() . '/checkout-fields-for-blocks.php' ),
			$asset_file['dependencies'],
			$asset_file['version']
		);
	}
}
