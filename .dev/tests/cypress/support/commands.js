import { getWindowObject } from '../helpers';

// Maintain WordPress logged in state
Cypress.Cookies.defaults( {
	preserve: /wordpress_.*/,
} );

/**
 * Clear WordPress Cookies (logout)
 */
Cypress.Commands.add( 'clearWordPressCookies', () => {
	cy.readFile( 'adminUserLoginCookiesFromCypress.json' )
		.then( ( cookies ) => {
			cookies.forEach( ( cookie ) => {
				cy.clearCookie( cookie.name );
			} );
		} );
} );

/**
 * Store the WordPress cookies in a .json file for later use.
 */
Cypress.Commands.add( 'getWordPressCookies', () => {
	cy.getCookies()
		.then( (cookies) => {
			cy.writeFile( 'adminUserLoginCookiesFromCypress.json', cookies );
		});
} );

/**
 * Set the WordPRess cookies in the browser, to maintain a logged in state.
 */
Cypress.Commands.add( 'setWordPressCookies', () => {
	cy.readFile( 'adminUserLoginCookiesFromCypress.json' )
		.then( ( cookies ) => {
			cookies.forEach( ( cookie ) => {
				// cy.log( JSON.stringify( cookie ) ); // See the cookie contents
				cy.setCookie( cookie.name, cookie.value, {
					domain: Cypress.env( 'domain' ),
					path: cookie.path,
					secure: cookie.secure,
					httpOnly: cookie.httpOnly,
					expiry: cookie.expiry
				} );
			} );
		} );
} );

/**
 * Login to the WordPRess site.
 */
Cypress.Commands.add( 'manualWordPressLogin', () => {
	cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin' );
	cy.get( '#user_login' ).type( Cypress.env( 'wpUsername' ) , { force: true } );
	cy.get( '#user_pass' ).type( Cypress.env( 'wpPassword' ), { force: true } );
	cy.get( '#wp-submit' ).click();
	cy.get( 'h1' ).contains( 'Dashboard' );
} );

/**
 * Upload a file.
 */
Cypress.Commands.add( 'uploadFile', ( fileName, fileType, selector ) => {
	cy.get( selector ).then( ( subject ) => {
		cy.fixture( fileName, 'hex' ).then( ( fileHex ) => {
			const fileBytes = hexStringToByte( fileHex );
			const testFile = new File( [ fileBytes ], fileName, {
				type: fileType,
			} );
			const dataTransfer = new DataTransfer();
			const el = subject[ 0 ];

			dataTransfer.items.add( testFile );
			el.files = dataTransfer.files;
		} );
	} );
} );

/**
 * Disable Gutenberg Tips.
 */
Cypress.Commands.add( 'disableGutenbergFeatures', () => {
	getWindowObject().then( ( safeWin ) => {
		// Enable 'Top Toolbar'
		if ( ! safeWin.wp.data.select( 'core/edit-post' ).isFeatureActive( 'fixedToolbar' ) ) {
			safeWin.wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fixedToolbar' );
		}

		if ( ! safeWin.wp.data.select( 'core/edit-post' ).isFeatureActive( 'welcomeGuide' ) ) {
			return;
		}

		safeWin.wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' );
		safeWin.wp.data.dispatch( 'core/editor' ).disablePublishSidebar();
	} );
} );

/**
 * From inside the WordPress editor open the CoBlocks Gutenberg editor panel.
 *
 * @param {string}  blockName   The name to find in the block inserter
 *                              e.g 'core/image' or 'coblocks/accordion'.
 * @param {boolean} clearEditor Should clear editor of all blocks
 */
Cypress.Commands.add( 'addBlockToPost', ( blockName, clearEditor = false ) => {
	const blockCategory = blockName.split( '/' )[ 0 ] || false;
	const blockID = blockName.split( '/' )[ 1 ] || false;

	if ( ! blockCategory || ! blockID ) {
		return;
	}

	if ( clearEditor ) {
		cy.clearBlocks();
	}

	cy.get( '.edit-post-header [aria-label="Add block"], .edit-site-header [aria-label="Add block"], .edit-post-header-toolbar__inserter-toggle' ).click();
	cy.get( '.block-editor-inserter__search-input,input.block-editor-inserter__search, .components-search-control__input, .editor-inserter__menu input[type="search"]' ).click( { force: true } ).type( blockID, { force: true } );

	const targetClassName = ( blockCategory === 'core' ? '' : `-${ blockCategory }` ) + `-${ blockID }`;
	cy.get( '.block-editor-block-types-list__item-title, .editor-block-types-list__item-title' ).contains( 'Image' ).click( { force: true } );

	// Make sure the block was added to our page
	cy.get( `[class*="-visual-editor"] [data-type="${ blockName }"]` ).should( 'exist' ).then( () => {
		// Then close the block inserter if still open.
		const inserterButton = Cypress.$( 'button[class*="__inserter-toggle"].is-pressed' );
		if ( !! inserterButton.length ) {
			cy.get( 'button[class*="__inserter-toggle"].is-pressed' ).click();
		}
	} );
} );

/**
 * Clear all blocks from the editor.
 */
Cypress.Commands.add( 'clearBlocks', () => {
	getWindowObject().then( ( safeWin ) => {
		safeWin.wp.data.dispatch( 'core/block-editor' ).removeBlocks(
			safeWin.wp.data.select( 'core/block-editor' ).getBlocks().map( ( block ) => block.clientId )
		);
	} );
} );

/**
 * From inside the WordPress editor open the editor panel.
 */
Cypress.Commands.add( 'savePage', () => {
	cy.get( '.edit-post-header__settings button.is-primary' ).click();

	cy.get( '.components-editor-notices__snackbar', { timeout: 120000 } ).should( 'not.be.empty' );

	// Reload the page to ensure that we're not hitting any block errors
	cy.reload();
} );

/**
 * From inside the WordPress editor open the editor panel.
 * To be used on: WordPress 5.4
 */
Cypress.Commands.add( 'savePage54', () => {
	cy.get( '.edit-post-header__settings button.is-primary' ).click();
	cy.get( 'button.editor-post-publish-button' ).click();

	cy.get( '.components-notice.is-success', { timeout: 120000 } ).should( 'not.be.empty' );

	// Reload the page to ensure that we're not hitting any block errors
	cy.reload();
} );

/**
 * View the currently edited page on the front of site.
 */
Cypress.Commands.add( 'viewPage', () => {
	cy.get( 'button[aria-label="Settings"]' ).then( ( settingsButton ) => {
		if ( ! Cypress.$( settingsButton ).hasClass( 'is-pressed' ) && ! Cypress.$( settingsButton ).hasClass( 'is-toggled' ) ) {
			cy.get( settingsButton ).click();
		}
	} );

	cy.openSettingsPanel( /permalink/i );

	cy.get( '.edit-post-post-link__link' ).then( ( pageLink ) => {
		const linkAddress = Cypress.$( pageLink ).attr( 'href' );
		cy.visit( linkAddress );
	} );
} );

/**
 * Open a certain settings panel in the right hand sidebar of the editor.
 *
 * @param {RegExp} panelText The panel label text to open. eg: Color Settings
 */
Cypress.Commands.add( 'openSettingsPanel', ( panelText ) => {
	cy.get( '.components-panel__body' )
		.contains( panelText )
		.then( ( $panelTop ) => {
			const $parentPanel = Cypress.$( $panelTop ).closest( 'div.components-panel__body' );
			if ( ! $parentPanel.hasClass( 'is-opened' ) ) {
				$panelTop.trigger( 'click' );
			}
		} );
} );

/**
 * Close a certain settings panel in the right hand sidebar of the editor.
 *
 * @param {RegExp} panelText The panel label text to open. eg: Color Settings
 */
Cypress.Commands.add( 'closeSettingsPanel', ( panelText ) => {
	cy.get( '.components-panel__body' )
		.contains( panelText )
		.then( ( $panelTop ) => {
			const $parentPanel = Cypress.$( $panelTop ).closest( 'div.components-panel__body' );
			if ( $parentPanel.hasClass( 'is-opened' ) ) {
				$panelTop.trigger( 'click' );
			}
		} );
} );

/**
 * Reset the plugin settings back to the defaults.
 */
Cypress.Commands.add( 'resetPluginSettings', ( waitForCache = true ) => {
	cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/options-general.php?page=lity-options' );
	cy.get( 'h1' ).should( 'contain', 'Lity - Responsive Lightboxes' );

	cy.get( '#lity-reset-plugin-settings' ).click();

	cy.on( 'window:alert', ( str ) => {
		expect( str ).to.equal( 'Are you sure you want to reset the plugin settings? This cannot be undone.' );
	} );

	cy.get( '.notice.notice-success' ).contains( 'Lity - Responsive Lightboxes settings successfully reset, and the cache has been cleared.' );

	// Give it a few second to let our admin notice display.
	cy.wait( 2500 );

	cy.reload();

	cy.get( '#lity-cache-rebuilding-notice' ).should( 'exist' );

	if ( waitForCache ) {
		cy.waitForCache();
	}
} );

/**
 * Wait for the cache notice to disappear, so we can be assured the cache has been rebuilt.
 */
Cypress.Commands.add( 'waitForCache', ( attempt = 0 ) => {
	// After 25 attempts, bail.
	if ( attempt > 25 ) {
		throw 'Failed';
	}

	const notice = Cypress.$( '#lity-cache-rebuilding-notice' );

	if ( notice.is( ':visible' ) ) {
		cy.wait( 5000 );
		cy.reload();
		cy.waitForCache( attempt + 1 )
	}
} );
