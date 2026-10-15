import { registerCheckoutBlock } from '@woocommerce/blocks-checkout';
import CheckoutFieldWrapper from '../components/CheckoutFieldWrapper';
import { useFieldState } from '../hooks/useFieldState';
import { createFieldTypeRegistrar } from './fieldTypeRegistry';

const registerFieldType = createFieldTypeRegistrar(
	( { metadata, component } ) =>
		registerCheckoutBlock( { metadata, component } ),
	( definition ) => typeof definition.component === 'function'
);

window.cffbCheckoutFields = window.cffbCheckoutFields || {};
window.cffbCheckoutFields.checkout = Object.freeze( {
	ready: true,
	extensionDataNamespace: 'checkout-fields-for-blocks',
	registerFieldType,
	components: Object.freeze( { CheckoutFieldWrapper } ),
	hooks: Object.freeze( { useFieldState } ),
} );
