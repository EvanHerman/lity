/**
 * Lity - Responsive Lightboxes
 * Lightbox Tests
 */

import * as helpers from '../../helpers';

describe( 'Test Lity lightbox', () => {

	before( () => {
		cy.manualWordPressLogin();
		cy.getWordPressCookies();
		cy.resetPluginSettings();
	} );

	after( () => {
		cy.clearWordPressCookies();
	} );

	it( 'opens', () => {
		const { imageBase } = helpers.upload.spec;

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin' );
		cy.location('pathname').should( 'match', /^\/wp-admin/ );

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/post-new.php?post_type=page' );
		cy.disableGutenbergFeatures();

		cy.get( '.editor-post-title__input' ).type( 'Test Image', { force: true } );

		cy.addBlockToPost( 'core/image' );

		helpers.upload.imageToBlock( 'core/image' );

		cy.get( 'figure.wp-block-image img[src*="http"]' ).should( 'have.attr', 'src' ).should( 'include', imageBase );

		cy.get( 'figure.wp-block-image' ).click();

		cy.get( 'button[aria-label="Settings"]' ).then( ( settingsButton ) => {
			if ( ! Cypress.$( settingsButton ).hasClass( 'is-pressed' ) && ! Cypress.$( settingsButton ).hasClass( 'is-toggled' ) ) {
				cy.get( settingsButton ).click();
			}
		} );

		// Image size select field.
		cy.get( '.components-panel__body .components-panel__body.is-opened:first-child select[id^=inspector-select-control-]' ).select( 'Full Size' );

		cy.savePage54();

		cy.viewPage();

		cy.get( '.lity-a11y-link' ).should( 'have.attr', 'aria-label').should( 'include', 'View Image' );

		cy.get( '.lity-a11y-link' ).click();

		cy.get( '.lity-opened' ).should( 'exist' );
		cy.get( '.lity-opened img[src*="http"]' ).should( 'have.attr', 'src' ).should( 'include', imageBase );
		cy.get( '.lity-info' ).should( 'not.exist' );

	} );

	it( 'shows title and caption', () => {
		const { imageBase } = helpers.upload.spec;

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/options-general.php?page=lity-options' );
		cy.get( 'h1' ).should( 'contain', 'Lity - Responsive Lightboxes' );

		cy.get( '#show_image_info').select( 'yes' );

		cy.get( 'input[type="submit"]' ).click();

		cy.get( '#setting-error-settings_updated' ).should( 'contain', 'Settings saved.' );

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/upload.php' );

		cy.get( 'h1' ).should( 'contain', 'Media Library' );

		cy.get( '.attachments .attachment:first-child' ).click();

		cy.get( 'label[data-setting="title"] input[type="text"]' ).clear().type( 'Tree Image' );
		cy.get( 'label[data-setting="caption"] textarea' ).clear().type( 'Tree Caption' );
		cy.get( 'label[data-setting="description"] textarea' ).clear().type( 'Tree Description' );
		cy.get( 'label[data-setting="title"] input[type="text"]' ).click();

		cy.get( '.save-complete' );

		// Navigate back to the page listings in descending order
		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/edit.php?post_type=page&orderby=date&order=desc' );

		// First page in the list. Force click becuase the 'view' link is hidden.
		cy.get( '#the-list tr:first-child .column-title span.view a' ).click( { force: true } );

		cy.get( '.lity-a11y-link' ).click();
		cy.get( '.lity-opened' ).should( 'exist' );

		cy.get( '.lity-info' ).should( 'exist' );
		cy.get( '.lity-info h4' ).contains( 'Tree Image' );
		cy.get( '.lity-info p' ).contains( 'Tree Caption' );

	} );

	it( 'shows title and description', () => {

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/options-general.php?page=lity-options' );
		cy.get( 'h1' ).should( 'contain', 'Lity - Responsive Lightboxes' );

		cy.get( '#caption_type').select( 'description' );

		cy.get( 'input[type="submit"]' ).click();

		cy.get( '#setting-error-settings_updated' ).should( 'contain', 'Settings saved.' );

		// Navigate back to the page listings in descending order
		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/edit.php?post_type=page&orderby=date&order=desc' );

		// First page in the list. Force click becuase the 'view' link is hidden.
		cy.get( '#the-list tr:first-child .column-title span.view a' ).click( { force: true } );

		cy.get( '.lity-a11y-link' ).click();
		cy.get( '.lity-opened' ).should( 'exist' );

		cy.get( '.lity-info' ).should( 'exist' );
		cy.get( '.lity-info h4' ).contains( 'Tree Image' );
		cy.get( '.lity-info p' ).contains( 'Tree Description' );

	} );

	it( 'closes when x is clicked', () => {

		// Navigate back to the page listings in descending order
		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/edit.php?post_type=page&orderby=date&order=desc' );

		// First page in the list. Force click becuase the 'view' link is hidden.
		cy.get( '#the-list tr:first-child .column-title span.view a' ).click( { force: true } );

		cy.get( '.lity-a11y-link' ).click();
		cy.get( '.lity-opened' ).should( 'exist' );

		cy.get( '.lity-close' ).should( 'exist' );
		cy.get( '.lity-close' ).click();

		cy.get( '.lity-opened' ).should( 'not.exist' );

	} );

	it( 'closes when the escape key is pressed', () => {

		// Navigate back to the page listings in descending order
		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/edit.php?post_type=page&orderby=date&order=desc' );

		// First page in the list. Force click becuase the 'view' link is hidden.
		cy.get( '#the-list tr:first-child .column-title span.view a' ).click( { force: true } );

		cy.get( '.lity-a11y-link' ).click();
		cy.get( '.lity-opened' ).should( 'exist' );

		// Escape key press.
		cy.get( 'body' ).type( '{esc}' );

		cy.get( '.lity-opened' ).should( 'not.exist' );

	} );

	it( "doesn't open img elements when excluded", () => {

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/options-general.php?page=lity-options' );
		cy.get( 'h1' ).should( 'contain', 'Lity - Responsive Lightboxes' );

		cy.resetPluginSettings( false );

		// Wait for js to initialize the fields
		cy.wait( 1000  );

		cy.get( 'label[for="excluded_element_selectors"]' ).parents( 'tr' ).find( '.tagify__input' ).click( { force: true } ).clear().type( 'img' );

		cy.get( 'input[type="submit"]' ).click();

		cy.get( '#setting-error-settings_updated' ).should( 'contain', 'Settings saved.' );

		// Navigate back to the page listings in descending order
		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/edit.php?post_type=page&orderby=date&order=desc' );

		// First page in the list. Force click becuase the 'view' link is hidden.
		cy.get( '#the-list tr:first-child .column-title span.view a' ).click( { force: true } );

		cy.get( '.lity-a11y-link' ).should( 'not.exist' );
		cy.get( '.wp-block-image img' ).click();
		cy.get( '.lity-opened' ).should( 'not.exist' );

	} );

	it( "settings save as expected", () => {

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/options-general.php?page=lity-options' );
		cy.get( 'h1' ).should( 'contain', 'Lity - Responsive Lightboxes' );

		cy.resetPluginSettings( false );

		// Use Full Size Images.
		cy.get( '#show_full_size' ).select( 'no' );

		// Use Background Images.
		cy.get( '#use_background_image' ).select( 'no' );

		// Show Image Info.
		cy.get( '#show_image_info' ).select( 'yes' );

		// Caption Type.
		cy.get( '#caption_type' ).select( 'description' );

		// Disabled On (Hello World!, Sample Page)
		cy.get( '#disabled_on' ).select( [ '1', '2' ], { force: true } );

		// Element Selectors.
		cy.get( 'label[for="element_selectors"]' ).parents( 'tr' ).find( '.tagify__input' ).click( { force: true } ).clear().type( '.include-element' );

		// Excluded Element Selectors.
		cy.get( 'label[for="excluded_element_selectors"]' ).parents( 'tr' ).find( '.tagify__input' ).click( { force: true } ).clear().type( '.exclude-element' );

		cy.get( 'input[type="submit"]' ).click();

		cy.get( '#setting-error-settings_updated' ).should( 'contain', 'Settings saved.' );

		// Verify the values saved from above.
		cy.get( '#show_full_size' ).should( 'have.value', 'no' );
		cy.get( '#use_background_image' ).should( 'have.value', 'no' );
		cy.get( '#show_image_info' ).should( 'have.value', 'yes' );
		cy.get( '#caption_type' ).should( 'have.value', 'description' );
		cy.get( '#disabled_on' ).invoke( 'val' ).should( 'deep.equal', [ '2', '1' ] );
		cy.get( '#element_selectors' ).invoke( 'val' ).should( 'equal', '[{"value":"img"},{"value":".include-element"}]' );
		cy.get( '#excluded_element_selectors' ).invoke( 'val' ).should( 'equal', '[{"value":".exclude-element"}]' );

	} );

} );
