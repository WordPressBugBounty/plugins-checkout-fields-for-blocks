<?php
/**
 * Template for displaying additional information in the order confirmation page.
 *
 * @var array<string, array{label: string, kind: string, value: string, display: string}> $additional_fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( ! empty( $additional_fields ) ) : ?>
	<h3><?php esc_html_e( 'Additional Information', 'checkout-fields-for-blocks' ); ?></h3>
	<div class="cffb__list">
		<?php foreach ( $additional_fields as $field ) : ?>
			<div class="cffb__list-node">
				<span class="cffb__field-label"><?php echo esc_html( $field['label'] ); ?></span>:
				<span class="cffb__field-value">
					<?php if ( $field['kind'] === \WPDesk\CBFields\Integration\DisplayValue::KIND_LINK ) : ?>
						<a href="<?php echo esc_url( $field['value'] ); ?>"><?php echo esc_html( $field['display'] ); ?></a>
					<?php else : ?>
						<?php echo nl2br( esc_html( $field['value'] ) ); ?>
					<?php endif; ?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
