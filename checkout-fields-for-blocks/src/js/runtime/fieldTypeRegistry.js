const isValidCommonDefinition = ( definition ) => {
	return (
		definition &&
		typeof definition === 'object' &&
		typeof definition.type === 'string' &&
		/^[a-z0-9][a-z0-9_-]*$/.test( definition.type ) &&
		typeof definition.blockName === 'string' &&
		definition.metadata &&
		definition.metadata.name === definition.blockName
	);
};

export const createFieldTypeRegistrar = ( register, isComplete ) => {
	const registeredTypes = new Set();
	const registeredBlocks = new Set();

	return ( definition ) => {
		if ( ! isValidCommonDefinition( definition ) || ! isComplete( definition ) ) {
			return false;
		}

		if (
			registeredTypes.has( definition.type ) ||
			registeredBlocks.has( definition.blockName )
		) {
			return false;
		}

		try {
			register( definition );
		} catch ( error ) {
			return false;
		}

		registeredTypes.add( definition.type );
		registeredBlocks.add( definition.blockName );

		return true;
	};
};
