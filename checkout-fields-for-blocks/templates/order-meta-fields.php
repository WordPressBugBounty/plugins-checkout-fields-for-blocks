<?php
/**
 * Template for displaying additional information in the order confirmation page.
 *
 * @var array<string, array{label: string, value: string}> $additional_fields
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
				<span class="cffb__field-value"><?php echo nl2br( esc_html( $field['value'] ) ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
