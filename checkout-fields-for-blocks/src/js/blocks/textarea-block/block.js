import { __ } from '@wordpress/i18n';
import { useState, useEffect, useMemo, useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { VALIDATION_STORE_KEY } from '@woocommerce/block-data';
import { Label, ValidationInputError } from '@woocommerce/blocks-components';
import clsx from 'clsx';
import { Textarea } from '../../components/textarea/textarea';
import CheckoutFieldWrapper from '../../components/CheckoutFieldWrapper';
import { useFieldState } from '../../hooks/useFieldState';

// Inner component that uses the context hooks
const TextareaInner = ( {
	fieldId,
	fieldName,
	metaName,
	label,
	placeholder,
	className,
	defaultValue,
	helpText,
	checkoutExtensionData,
} ) => {
	const { setExtensionData } = checkoutExtensionData;
	const [ inputValue, setInputValue ] = useState( defaultValue || '' );

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

	useEffect( () => {
		// Only set data when field is visible
		setExtensionData( 'checkout-fields-for-blocks', metaName, inputValue );
	}, [ setExtensionData, metaName, inputValue ] );

	useEffect( () => {
		validateField( inputValue, false );
	}, [ validateField, inputValue ] );

	const requiredAttributes = useMemo(
		() => ( {
			'aria-required': isRequired,
			...( isRequired && { required: true } ),
		} ),
		[ isRequired ]
	);

	return (
		<div
			className={ clsx( className, {
				'has-error': hasError,
			} ) }
		>
			<Label
				label={ label }
				screenReaderLabel={ label }
				wrapperElement="label"
				wrapperProps={ {
					htmlFor: fieldId,
				} }
				htmlFor={ fieldId }
			/>
			<Textarea
				id={ fieldId }
				value={ inputValue ? inputValue : '' }
				placeholder={ placeholder }
				name={ fieldName }
				onChange={ handleValueChange }
				onBlur={ handleBlur }
				aria-invalid={ hasError }
				aria-errormessage={
					hasError ? storeValidationErrorId : undefined
				}
				{ ...requiredAttributes }
				data-field-name={ metaName }
			/>

			{ helpText && <p className="help-text">{ helpText }</p> }

			<ValidationInputError
				propertyName={ validationErrorId }
				elementId={ validationErrorId }
			/>
		</div>
	);
};

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
			fieldName={ label || fieldName || 'Textarea' }
			setExtensionData={ setExtensionData }
			metaName={ metaName }
		>
			<TextareaInner { ...props } />
		</CheckoutFieldWrapper>
	);
};

export default FrontendBlock;
