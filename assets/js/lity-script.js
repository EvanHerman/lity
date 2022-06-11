const lityMediaData = JSON.parse( lityScriptData.mediaData );

/**
 * Lity Scripts
 *
 * @since 1.0.0
 */
var lityScript = {

	/**
	 * Initialize the image with data-lity attributes
	 *
	 * @since 1.0.0
	 */
	init: function() {

		jQuery( lityScriptData.imgSelectors ).not( lityScriptData.options.excluded_element_selectors ).attr( 'data-lity', '' );

	},

	/**
	 * Add a data-lity-target attribute with the proper full size image URL.
	 *
	 * @since 1.0.0
	 */
	fullSizeImages: function() {

		if ( 'no' === lityScriptData.options.show_full_size ) {
			return;
		}

		jQuery( lityScriptData.imgSelectors ).each( function( img ) {
			if ( ! lityMediaData ) {
				return;
			}

			let imgSrc = jQuery( this ).attr( 'src' );
			let imgObj = [];

			lityMediaData.forEach( ( media, index ) => {
				if ( media.urls.includes( imgSrc ) ) {
					imgObj.push( lityMediaData[ index ] );
				}
			} );

			if ( imgObj.length ) {
				// make lity lightboxes show full sized versions of the image
				jQuery( this ).attr( 'data-lity-target', imgObj[0].urls[0] );
			}
		} );

	},

	/**
	 * Append title and caption image info.
	 *
	 * @since 1.0.0
	 */
	appendImageInfo: function() {

		if ( 'no' === lityScriptData.options.show_image_info ) {
			return;
		}

		jQuery( lityScriptData.imgSelectors ).each( function( img ) {
			let imgSrc = jQuery( this ).attr( 'src' );

			let imgObj = [];

			lityMediaData.forEach( ( media, index ) => {
				if ( media.urls.includes( imgSrc ) ) {
					imgObj.push( lityMediaData[ index ] );
				}
			} );

			if ( imgObj.length ) {

				if ( !! imgObj[0].title ) {
					jQuery( this ).attr( 'data-lity-title', imgObj[0].title );
				}

				if ( !! imgObj[0].caption ) {
					jQuery( this ).attr( 'data-lity-description', imgObj[0].caption );
				}
			}
		} );

	},

	/**
	 * Show the image info in the lightbox when clicked.
	 *
	 * @since 1.0.0
	 */
	showImageInfo: function( event, lightbox ) {

		const triggerElement = lightbox.opener();
		const title = triggerElement.data( 'lity-title' );
		const description = triggerElement.data( 'lity-description' );

		if ( !! title || !! description ) {
			jQuery( '.lity-content' ).addClass( 'lity-image-info' ).append( '<div class=lity-info></div>' );
		}

		if ( !! title ) {
			jQuery( '.lity-info' ).append( '<h4>' + triggerElement.data( 'lity-title' ) + '</h4>' );
		}

		if ( !! description ) {
			jQuery( '.lity-info' ).append( '<p>' + triggerElement.data( 'lity-description' ) + '</p>' );
		}

	}

};

jQuery( document ).on( 'ready', lityScript.init );
jQuery( document ).on( 'ready', lityScript.fullSizeImages );
jQuery( document ).on( 'ready', lityScript.appendImageInfo );
jQuery( document ).on( 'lity:ready', lityScript.showImageInfo );
