import { __ } from '@wordpress/i18n';
import { PanelBody, Button } from '@wordpress/components';

const teaserData = window.cffbTeaser || {};

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
				href={ teaserData.conditionalLogicUrl }
				target="_blank"
				rel="noopener noreferrer"
			>
				{ __(
					'Buy Conditional Logic add-on',
					'checkout-fields-for-blocks'
				) }
			</Button>
		</PanelBody>
	);
};
