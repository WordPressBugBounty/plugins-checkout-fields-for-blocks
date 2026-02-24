import {
	useContext,
	useState,
	useCallback,
	useEffect,
} from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { VALIDATION_STORE_KEY } from '@woocommerce/block-data';
import { runValidations } from '../validations/validations';
import FieldStateContext from '../context/FieldStateContext';

export const useFieldState = ( fieldId, metaName ) => {
	const { validationSettings, isRequired, hasRequiredLogic, isVisible } =
		useContext( FieldStateContext );
	const [ hasUserInteracted, setHasUserInteracted ] = useState( false );

	const { setValidationErrors, clearValidationError } =
		useDispatch( VALIDATION_STORE_KEY );
	const validationErrorId = `${ metaName }-${ fieldId }`;

	const validateField = useCallback(
		( value, forceValidation = false ) => {
			const mergedValidationSettings = { ...validationSettings };
			if ( hasRequiredLogic ) {
				mergedValidationSettings.required = { enabled: isRequired };
			}

			const validationError = runValidations(
				value,
				mergedValidationSettings
			);

			if ( validationError ) {
				setValidationErrors( {
					[ validationErrorId ]: {
						message: validationError,
						hidden: ! forceValidation,
					},
				} );
				return false;
			} else {
				clearValidationError( validationErrorId );
				return true;
			}
		},
		[
			validationSettings,
			isRequired,
			hasRequiredLogic,
			validationErrorId,
			clearValidationError,
			setValidationErrors,
		]
	);

	const handleUserInteraction = useCallback( () => {
		if ( ! hasUserInteracted ) {
			setHasUserInteracted( true );
		}
	}, [ hasUserInteracted ] );

	return {
		validateField,
		handleUserInteraction,
		validationErrorId,
		isRequired,
		isVisible,
	};
};
