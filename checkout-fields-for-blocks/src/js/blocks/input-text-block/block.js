/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useMemo, useCallback } from '@wordpress/element';
import { ValidatedTextInput } from '@woocommerce/blocks-checkout';

/**
 * Internal dependencies
 */
import CheckoutFieldWrapper from '../../components/CheckoutFieldWrapper';
import { useFieldState } from '../../hooks/useFieldState';

// Inner component that uses the context hooks
const TextInputInner = ( {
	fieldId,
	fieldName,
	metaName,
	label,
	className,
	defaultValue,
	inputType,
	helpText,
	checkoutExtensionData,
} ) => {
	const { setExtensionData } = checkoutExtensionData;
	const [ inputValue, setInputValue ] = useState( defaultValue || '' );

	const { validateField, validationErrorId, isRequired } = useFieldState(
		fieldId,
		metaName
	);

	// Handle value changes
	const handleValueChange = useCallback(
		( value ) => {
			setInputValue( value );
			validateField( value );
		},
		[ validateField ]
	);

	const handleBlur = useCallback( () => {
		validateField( inputValue, true );
	}, [ validateField, inputValue ] );

	// Extension data management with conditional logic support
	useEffect( () => {
		// Only set data when field is visible
		setExtensionData( 'checkout-fields-for-blocks', metaName, inputValue );
	}, [ setExtensionData, metaName, inputValue ] );

	useEffect( () => {
		validateField( inputValue, false );
	}, [ validateField, inputValue ] );

	// Custom validation for WooCommerce ValidatedTextInput
	const customValidationProp = useCallback(
		( inputObject ) => {
			return validateField( inputObject.value, true );
		},
		[ validateField ]
	);

	const requiredAttributes = useMemo(
		() => ( {
			'aria-required': isRequired,
			...( isRequired && { required: true } ),
		} ),
		[ isRequired ]
	);

	return (
		<div className={ className }>
			<ValidatedTextInput
				id={ fieldId }
				type={ inputType }
				name={ fieldName }
				label={ label }
				value={ inputValue }
				customValidation={ customValidationProp }
				help={ helpText }
				onChange={ handleValueChange }
				onBlur={ handleBlur }
				errorId={ validationErrorId }
				data-field-name={ metaName }
				{ ...requiredAttributes }
			/>
		</div>
	);
};

// Outer component that wraps with CheckoutFieldWrapper
const FrontendBlock = ( props ) => {
	const {
		conditionalLogic,
		validationSettings,
		label,
		fieldName,
		metaName,
		checkoutExtensionData,
	} = props;
	const { setExtensionData } = checkoutExtensionData;

	return (
		<CheckoutFieldWrapper
			conditionalLogic={ conditionalLogic }
			validationSettingsRaw={ validationSettings }
			fieldName={ label || fieldName || 'Text Input' }
			setExtensionData={ setExtensionData }
			metaName={ metaName }
		>
			<TextInputInner { ...props } />
		</CheckoutFieldWrapper>
	);
};

export default FrontendBlock;
export { TextInputInner };
