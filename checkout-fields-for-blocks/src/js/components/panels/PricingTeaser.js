import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';

const teaserData = window.cffbTeaser || {};

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
				href={ teaserData.pricingUrl }
				target="_blank"
				rel="noopener noreferrer"
			>
				{ __(
					'Buy Pricing add-on',
					'checkout-fields-for-blocks'
				) }
			</Button>
		</PanelBody>
	);
};
