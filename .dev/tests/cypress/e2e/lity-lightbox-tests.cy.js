/**
 * Lity - Responsive Lightboxes
 * Lightbox Tests
 */

import * as helpers from '../helpers';

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

		cy.visit( Cypress.env( 'localTestURL' ) + 'wp-admin' );
		cy.location('pathname').should( 'match', /^\/wp-admin/ );

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/post-new.php?post_type=page' );
		cy.disableGutenbergFeatures();

		cy.get( '.wp-block-post-title' ).type( 'Test Image' );

		cy.addBlockToPost( 'core/image' );

		helpers.upload.imageToBlock( 'core/image' );

		cy.get( 'figure[data-type="core/image"] img[src*="http"]' ).should( 'have.attr', 'src' ).should( 'include', imageBase );

		cy.get( 'figure[data-type="core/image"]' ).click();

		cy.get( 'button[aria-label="Settings"]' ).then( ( settingsButton ) => {
			if ( ! Cypress.$( settingsButton ).hasClass( 'is-pressed' ) && ! Cypress.$( settingsButton ).hasClass( 'is-toggled' ) ) {
				cy.get( settingsButton ).click();
			}
		} );

		// Image size select field.
		cy.get( 'select[id^=inspector-select-control-]' ).select( 'full' );

		cy.savePage();

		cy.viewPage();

		cy.get( '.lity-a11y-link' ).should( 'have.attr', 'aria-label').should( 'include', 'View Image' );

		cy.get( '.lity-a11y-link' ).click();

		cy.get( '.lity-opened' ).should( 'exist' );
		cy.get( '.lity-info' ).should( 'not.exist' );

	} );

	it( 'shows title and caption', () => {
		const { imageBase } = helpers.upload.spec;

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/options-general.php?page=lity-options' );
		cy.get( 'h1' ).should( 'contain', 'Lity - Responsive Lightboxes' );

		cy.get( '#show_image_info').select( 'yes' );

		cy.get( 'input[type="submit"]' ).click();

		cy.get( 'div.notice-success' ).should( 'contain', 'Settings saved.' );

		cy.visit( Cypress.env( 'localTestURL' ) + '/wp-admin/upload.php' );

		cy.get( 'h1' ).should( 'contain', 'Media Library' );

		cy.get( '.attachments-wrapper li:first-child' ).click();

		cy.get( '#attachment-details-two-column-title' ).clear().type( 'Tree Image' );
		cy.get( '#attachment-details-two-column-caption' ).clear().type( 'Tree Caption' );
		cy.get( '#attachment-details-two-column-description' ).clear().type( 'Tree Description' );
		cy.get( '#attachment-details-two-column-alt-text' ).click();

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

		cy.get( 'div.notice-success' ).should( 'contain', 'Settings saved.' );

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

} );
