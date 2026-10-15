/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { Edit } from './edit';
import { Save } from '../../components/Save';
import { fieldIcon } from '../../components/FieldIcon';
import metadata from './block.json';
import './style.scss';

registerBlockType( metadata, {
	icon: fieldIcon,
	edit: Edit,
	save: Save,
} );
