import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import { generateSlug } from '../../utils/string';

export const GeneralPanel = ( { attributes, setAttributes, children } ) => {
	const { fieldName, metaName, isMetaNameLocked } = attributes;
	return (
		<PanelBody
			title={ __( 'General Settings', 'checkout-fields-for-blocks' ) }
		>
			<TextControl
				label={ __( 'Field name', 'checkout-fields-for-blocks' ) }
				value={ fieldName }
				onChange={ ( newFieldName ) => {
					const newAttributes = { fieldName: newFieldName };

					// Only update metaName if it's not locked
					if ( ! isMetaNameLocked ) {
						newAttributes.metaName =
							'_meta_' + generateSlug( newFieldName );
					}

					setAttributes( newAttributes );
				} }
				onBlur={ () => {
					// Lock metaName when user finishes editing field name
					if (
						fieldName &&
						fieldName.trim() !== '' &&
						! isMetaNameLocked
					) {
						setAttributes( { isMetaNameLocked: true } );
					}
				} }
			/>

			<TextControl
				label={ __( 'Meta name', 'checkout-fields-for-blocks' ) }
				value={ metaName }
				disabled={ isMetaNameLocked }
				help={
					isMetaNameLocked
						? __(
								'Meta name can not be changed.',
								'checkout-fields-for-blocks'
						  )
						: __(
								"Field's unique identifier. Locks when you finish editing.",
								'checkout-fields-for-blocks'
						  )
				}
			/>
			{ children }
		</PanelBody>
	);
};
