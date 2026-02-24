import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';

export const ConditionalLogicTeaser = () => {
	return (
		<PanelBody
			title={ __( 'Conditional Logic (Pro)', 'checkout-fields-for-blocks' ) }
			initialOpen={ false }
		>
			<p>
				{ __(
					'Display this field only when specific conditions are met.',
					'checkout-fields-for-blocks'
				) }
			</p>
			<Button
				isPrimary
				href="https://your-plugin-website.com/pro"
				target="_blank"
				rel="noopener noreferrer"
			>
				{ __(
					'Upgrade to PRO to Unlock',
					'checkout-fields-for-blocks'
				) }
			</Button>
		</PanelBody>
	);
};
