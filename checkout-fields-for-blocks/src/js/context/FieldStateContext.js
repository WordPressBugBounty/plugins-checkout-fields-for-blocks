import { createContext } from '@wordpress/element';

const FieldStateContext = createContext( {
	validationSettings: {},
	isRequired: false,
	hasRequiredLogic: false,
	isVisible: true,
} );

export default FieldStateContext;
