/**
 * Upload helper object. Contains image fixture spec and uploader function.
 * `helpers.upload.testImage` Object containing image spec.
 */
export const upload = {
	/**
	 * Upload image to input element.
	 *
	 * @param {string} blockName The name of the block that is upload target
	 *                           e.g 'core/image' or 'coblocks/accordion'.
	 */
	imageToBlock: ( blockName ) => {
		const { fileName, pathToFixtures } = upload.spec;
		cy.fixture( pathToFixtures + fileName, { encoding: null } ).then( ( fileContent ) => {
			cy.get( `[data-type="${ blockName }"] input[type="file"]` ).first()
				.selectFile( { contents: fileContent, fileName: pathToFixtures + fileName, mimeType: 'image/png' }, { force: true } );

			// Now validate upload is complete and is not a blob.
			cy.get( `[class*="-visual-editor"] [data-type="${ blockName }"] [src^="http"]` );
		} );
	},

	spec: {
		fileName: 'tree.jpg',
		imageBase: 'tree',
		pathToFixtures: './images/',
	},
};

/**
 * Safely obtain the window object or error
 * when the window object is not available.
 */
export function getWindowObject() {
	const editorUrlStrings = [ 'post-new.php', 'action=edit' ];
	return cy.window().then( ( win ) => {
		const isEditorPage = editorUrlStrings.filter( ( str ) => win.location.href.includes( str ) );

		if ( isEditorPage.length === 0 ) {
			throw new Error( 'Check the previous test, window property was invoked outside of Editor.' );
		}

		if ( ! win?.wp ) {
			throw new Error( 'Window property was invoked within Editor but `win.wp` is not defined.' );
		}

		return win;
	} );
}
