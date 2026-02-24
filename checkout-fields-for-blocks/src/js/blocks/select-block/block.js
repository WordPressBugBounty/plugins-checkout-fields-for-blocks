import { useState, useEffect, useMemo, useCallback } from '@wordpress/element';
import { Select } from '../../components/select/select';
import CheckoutFieldWrapper from '../../components/CheckoutFieldWrapper';
import { useFieldState } from '../../hooks/useFieldState';

// Inner component that uses the context hooks
const SelectInner = ( {
	className,
	label,
	helpText,
	fieldId,
	fieldName,
	defaultValue,
	metaName,
	checkoutExtensionData,
	options: optionsRaw,
	placeholder,
} ) => {
	const { setExtensionData } = checkoutExtensionData;
	const [ inputValue, setInputValue ] = useState( defaultValue || '' );

	const { validateField, validationErrorId, isRequired } = useFieldState(
		fieldId,
		metaName
	);

	const options = useMemo(
		() => JSON.parse( optionsRaw || '[]' ),
		[ optionsRaw ]
	);

	const handleValueChange = useCallback(
		( value ) => {
			setInputValue( value );
			validateField( value );
		},
		[ validateField ]
	);

	useEffect( () => {
		// Only set data when field is visible
		setExtensionData( 'checkout-fields-for-blocks', metaName, inputValue );
	}, [ setExtensionData, metaName, inputValue ] );

	useEffect( () => {
		validateField( inputValue, false );
	}, [ validateField, inputValue ] );

	return (
		<Select
			id={ fieldId }
			name={ fieldName }
			label={ label }
			placeholder={ placeholder }
			value={ inputValue }
			onChange={ handleValueChange }
			options={ options }
			readOnly={ false }
			required={ isRequired }
			help={ helpText }
			errorId={ validationErrorId }
			externalValidation={ true }
			className={ className }
			data-field-name={ metaName }
		/>
	);
};

// Outer component that wraps with CheckoutFieldWrapper
export const FrontendBlock = ( props ) => {
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
			fieldName={ label || fieldName || 'Select' }
			setExtensionData={ setExtensionData }
			metaName={ metaName }
		>
			<SelectInner { ...props } />
		</CheckoutFieldWrapper>
	);
};

export default FrontendBlock;
export { SelectInner };
