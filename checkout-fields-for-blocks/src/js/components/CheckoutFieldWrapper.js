/**
 * Checkout Field Wrapper for Frontend Blocks
 */
import { useMemo, useEffect } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import FieldStateContext from '../context/FieldStateContext';

const useParsedConditionalLogic = ( conditionalLogic ) => {
	return useMemo( () => {
		if ( ! conditionalLogic ) return null;

		if ( typeof conditionalLogic === 'string' ) {
			try {
				return JSON.parse( conditionalLogic );
			} catch ( e ) {
				console.warn(
					'Failed to parse conditional logic:',
					conditionalLogic
				);
				return null;
			}
		}

		return conditionalLogic;
	}, [ conditionalLogic ] );
};

const DefaultLogicContextProvider = ( { children } ) => <>{ children }</>;

const useDefaultConditionalLogic = () => ( {
	isVisible: true,
	isRequired: false,
	hasRequiredLogic: false,
} );

const FieldStateEvaluator = ( {
	conditionalLogic,
	validationSettingsRaw,
	children,
	fieldName,
	setExtensionData,
	metaName,
} ) => {
	const useConditionalLogic = applyFilters(
		'checkoutFields.hooks.useConditionalLogic',
		useDefaultConditionalLogic
	);

	const validationSettings = useMemo( () => {
		try {
			return JSON.parse( validationSettingsRaw || '{}' );
		} catch ( e ) {
			console.warn(
				'Invalid validation settings:',
				validationSettingsRaw
			);
			return {};
		}
	}, [ validationSettingsRaw ] );

	const parsedConditionalLogic =
		useParsedConditionalLogic( conditionalLogic );
	const { isVisible, isRequired, hasRequiredLogic } = useConditionalLogic(
		parsedConditionalLogic,
		fieldName
	);

	const contextValue = useMemo(
		() => ( {
			validationSettings,
			isRequired,
			hasRequiredLogic,
			isVisible,
		} ),
		[ validationSettings, isRequired, hasRequiredLogic, isVisible ]
	);

	useEffect( () => {
		if ( ! isVisible ) {
			setExtensionData( 'checkout-fields-for-blocks', metaName, '' );
		}
	}, [ isVisible, setExtensionData, metaName ] );

	if ( ! isVisible ) {
		return null; // Important: completely remove from DOM
	}

	return (
		<FieldStateContext.Provider value={ contextValue }>
			{ children }
		</FieldStateContext.Provider>
	);
};

/**
 * Checkout Field Wrapper Component
 */
export default function CheckoutFieldWrapper( {
	conditionalLogic,
	validationSettingsRaw,
	children,
	fieldName,
	setExtensionData,
	metaName,
} ) {
	const LogicContextProvider = applyFilters(
		'checkoutFields.components.logicContextProvider',
		DefaultLogicContextProvider
	);

	return (
		<LogicContextProvider>
			<FieldStateEvaluator
				conditionalLogic={ conditionalLogic }
				validationSettingsRaw={ validationSettingsRaw }
				fieldName={ fieldName }
				setExtensionData={ setExtensionData }
				metaName={ metaName }
			>
				{ children }
			</FieldStateEvaluator>
		</LogicContextProvider>
	);
}
