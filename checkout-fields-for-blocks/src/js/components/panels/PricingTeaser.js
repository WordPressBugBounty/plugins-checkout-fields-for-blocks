import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';

export const PricingTeaser = () => {
	return (
		<PanelBody
			title={ __( 'Pricing Settings (Pro)', 'checkout-fields-for-blocks' ) }
			initialOpen={ false }
		>
			<p>
				{ __(
					'Add pricing rules to this field.',
					'checkout-fields-for-blocks'
				) }
			</p>
			<Button
				isPrimary
				href="https://your-plugin-website.com/pricing"
				target="_blank"
				rel="noopener noreferrer"
			>
				{ __(
					'Upgrade to PRICING to Unlock',
					'checkout-fields-for-blocks'
				) }
			</Button>
		</PanelBody>
	);
};
