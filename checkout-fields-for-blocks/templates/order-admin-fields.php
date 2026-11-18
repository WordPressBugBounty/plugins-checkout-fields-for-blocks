<?php
/**
 * Admin order field summary and editor.
 *
 * @var array<int, array<string, mixed>> $fields
 * @var bool $has_editable_fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cffb-order-fields">
	<h3>
		<?php esc_html_e( 'Additional Information', 'checkout-fields-for-blocks' ); ?>
		<?php if ( $has_editable_fields ) : ?>
			<button type="button" class="button-link cffb-order-fields__toggle" data-edit-label="<?php esc_attr_e( 'Edit', 'checkout-fields-for-blocks' ); ?>" data-cancel-label="<?php esc_attr_e( 'Cancel', 'checkout-fields-for-blocks' ); ?>" aria-expanded="false"><?php esc_html_e( 'Edit', 'checkout-fields-for-blocks' ); ?></button>
		<?php endif; ?>
	</h3>
	<div class="cffb-order-fields__view">
		<?php foreach ( $fields as $field ) : ?>
			<?php if ( $field['display'] !== '' ) : ?>
				<p class="cffb-order-fields__field">
					<strong><?php echo esc_html( $field['label'] ); ?>:</strong>
					<?php if ( $field['kind'] === \WPDesk\CBFields\Integration\DisplayValue::KIND_LINK ) : ?>
						<a href="<?php echo esc_url( $field['url'] ); ?>"><?php echo esc_html( $field['display'] ); ?></a>
					<?php else : ?>
						<?php echo nl2br( esc_html( $field['display'] ) ); ?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php if ( $has_editable_fields ) : ?>
		<div class="cffb-order-fields__editor" hidden>
			<?php wp_nonce_field( 'cffb_save_order_fields', 'cffb_order_fields_nonce' ); ?>
			<input type="hidden" name="cffb_order_fields_editing" value="1" disabled>
			<fieldset disabled>
				<?php foreach ( $fields as $field ) : ?>
					<?php if ( $field['edit'] ) : ?>
						<div class="cffb-order-fields__edit-field">
							<strong><?php echo esc_html( $field['label'] ); ?></strong>
							<input type="hidden" name="cffb_rendered_fields[]" value="<?php echo esc_attr( $field['key'] ); ?>">
							<div class="cffb-order-fields__control"><?php echo $field['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Registered editor output is trusted. ?></div>
						</div>
					<?php elseif ( $field['display'] !== '' ) : ?>
						<p class="cffb-order-fields__readonly">
							<strong><?php echo esc_html( $field['label'] ); ?>:</strong>
							<?php if ( $field['kind'] === \WPDesk\CBFields\Integration\DisplayValue::KIND_LINK ) : ?>
								<a href="<?php echo esc_url( $field['url'] ); ?>"><?php echo esc_html( $field['display'] ); ?></a>
							<?php else : ?>
								<?php echo nl2br( esc_html( $field['display'] ) ); ?>
							<?php endif; ?>
						</p>
					<?php endif; ?>
				<?php endforeach; ?>
				<p><?php esc_html_e( 'Update the order to save these changes.', 'checkout-fields-for-blocks' ); ?></p>
			</fieldset>
		</div>
	<?php endif; ?>
</div>
