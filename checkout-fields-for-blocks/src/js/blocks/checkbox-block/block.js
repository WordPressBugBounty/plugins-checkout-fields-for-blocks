/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import { CheckboxControl } from '@woocommerce/blocks-components';
import { useSelect } from '@wordpress/data';
import { VALIDATION_STORE_KEY } from '@woocommerce/block-data';
/**
 * Internal dependencies
 */
import CheckoutFieldWrapper from '../../components/CheckoutFieldWrapper';
import { useFieldState } from '../../hooks/useFieldState';

// Inner component that uses the context hooks
const CheckboxInner = ( props ) => {
	const {
		fieldId,
		metaName,
		label,
		className,
		defaultValue,
		isChecked,
		checkoutExtensionData,
	} = props;
	const { setExtensionData } = checkoutExtensionData;
	const [ checked, setChecked ] = useState( !! isChecked );

	const { validateField, validationErrorId, isRequired } = useFieldState(
		fieldId,
		metaName
	);

	const { validationError, validationErrorId: storeValidationErrorId } =
		useSelect(
			( select ) => {
				const store = select( VALIDATION_STORE_KEY );
				return {
					validationError:
						store.getValidationError( validationErrorId ),
					validationErrorId:
						store.getValidationErrorId( validationErrorId ),
				};
			},
			[ validationErrorId ]
		);

	const hasError = !! (
		validationError?.message && ! validationError?.hidden
	);

	useEffect( () => {
		if ( checked ) {
			setExtensionData(
				'checkout-fields-for-blocks',
				metaName,
				defaultValue
			);
		} else {
			setExtensionData( 'checkout-fields-for-blocks', metaName, '' );
		}
	}, [ checked, setExtensionData, metaName, defaultValue ] );

	useEffect( () => {
		validateField( checked ? defaultValue : '', false );
	}, [ validateField, checked, defaultValue ] );

	return (
		<div className={ className }>
			<CheckboxControl
				id={ fieldId }
				name={ metaName }
				checked={ checked }
				onChange={ () => setChecked( ! checked ) }
				hasError={ hasError }
				data-field-name={ metaName }
				required={ isRequired }
			>
				<span
					dangerouslySetInnerHTML={ {
						__html: label,
					} }
				/>
			</CheckboxControl>
		</div>
	);
};

// Outer component that wraps with CheckoutFieldWrapper
const FrontendBlock = ( props ) => {
	const {
		conditionalLogic,
		validationSettings,
		label,
		metaName,
		checkoutExtensionData,
	} = props;
	const { setExtensionData } = checkoutExtensionData;

	return (
		<CheckoutFieldWrapper
			conditionalLogic={ conditionalLogic }
			validationSettingsRaw={ validationSettings }
			fieldName={ label || 'Checkbox' }
			setExtensionData={ setExtensionData }
			metaName={ metaName }
		>
			<CheckboxInner { ...props } />
		</CheckoutFieldWrapper>
	);
};

export default FrontendBlock;
