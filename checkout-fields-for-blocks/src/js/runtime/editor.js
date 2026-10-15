import { registerBlockType } from '@wordpress/blocks';
import { Save } from '../components/Save';
import { fieldIcon } from '../components/FieldIcon';
import {
	GeneralPanel,
	ValidationPanel,
	DisplayPanel,
	OptionsPanel,
	ConditionalLogicTeaser,
	PricingTeaser,
} from '../components/panels';
import { getParentBlockName } from '../utils/block';
import { createFieldTypeRegistrar } from './fieldTypeRegistry';

const registerFieldType = createFieldTypeRegistrar(
	( { metadata, settings } ) => registerBlockType( metadata, settings ),
	( definition ) =>
		definition.settings &&
		typeof definition.settings.edit === 'function' &&
		typeof definition.settings.save === 'function'
);

window.cffbCheckoutFields = window.cffbCheckoutFields || {};
window.cffbCheckoutFields.editor = Object.freeze( {
	ready: true,
	registerFieldType,
	icons: Object.freeze( { field: fieldIcon } ),
	components: Object.freeze( {
		Save,
		GeneralPanel,
		ValidationPanel,
		DisplayPanel,
		OptionsPanel,
		ConditionalLogicTeaser,
		PricingTeaser,
	} ),
	utilities: Object.freeze( { getParentBlockName } ),
} );
