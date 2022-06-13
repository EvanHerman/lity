( function( $ ) {
	const lityMediaData = JSON.parse( lityScriptData.mediaData );

	/**
	 * Lity Scripts
	 *
	 * @type {Object}
	 * @since 1.0.0
	 */
	const lityScript = {

		/**
		 * Initialize the image with data-lity attributes
		 *
		 * @since 1.0.0
		 */
		init: function() {

			$( lityScriptData.imgSelectors ).not( lityScriptData.options.excluded_element_selectors ).attr( 'data-lity', '' );

		},

		/**
		 * Add a data-lity-target attribute with the proper full size image URL.
		 *
		 * @since 1.0.0
		 */
		fullSizeImages: function() {

			if ( 'no' === lityScriptData.options.show_full_size || ! lityMediaData ) {
				return;
			}

			$( lityScriptData.imgSelectors ).each( function( img ) {
				let imgObj = helpers.findImageObj( $( this ).attr( 'src' ) );

				if ( Object.keys( imgObj ).length > 0 ) {
					// Ensure lity lightboxes show full sized versions of the image.
					$( this ).attr( 'data-lity-target', imgObj.urls[0] );
				}
			} );

		},

		/**
		 * Add a HTML element to display the selected image as the background, in a nicer way.
		 *
		 * @since 1.0.0
		 */
		backgroundImages: function( event, lightbox ) {

			if ( 'no' === lityScriptData.options.use_background_image ) {
				return;
			}

			const triggerElement = lightbox.opener();

			$( '.lity-wrap' ).before( `<div class="lity-lightbox-background" style="background-image: url(${triggerElement[0].currentSrc});"></div>` );

		},

		/**
		 * Append title and caption image info.
		 *
		 * @since 1.0.0
		 */
		appendImageInfo: function() {

			if ( 'no' === lityScriptData.options.show_image_info || ! lityMediaData ) {
				return;
			}

			$( lityScriptData.imgSelectors ).each( function( img ) {

				let imgObj = helpers.findImageObj( $( this ).attr( 'src' ) );

				if ( Object.keys( imgObj ).length > 0 ) {

					if ( !! imgObj.title ) {
						$( this ).attr( 'data-lity-title', imgObj.title );
					}

					if ( !! imgObj.caption ) {
						$( this ).attr( 'data-lity-description', imgObj.caption );
					}

					if ( !! imgObj.custom_data && Object.keys( imgObj.custom_data ).length ) {

						for ( const [ key, value ] of Object.entries( imgObj.custom_data ) ) {
							$( this ).attr( `data-lity-custom-${key}`, `${value.element_wrap}:${value.content}` );
						}

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
			const customData = helpers.getImageCustomData( triggerElement );

			if ( !! title || !! description ) {
				$( '.lity-content' ).addClass( 'lity-image-info' ).append( '<div class=lity-info></div>' );
			}

			if ( !! title ) {
				$( '.lity-info' ).append( '<h4>' + triggerElement.data( 'lity-title' ) + '</h4>' );
			}

			if ( !! description ) {
				$( '.lity-info' ).append( '<p>' + triggerElement.data( 'lity-description' ) + '</p>' );
			}

			if ( customData.length ) {
				customData.forEach( data => {
					$( '.lity-info' ).append( `<${data.element} class="${data.class}">${data.content}</${data.element}>` );
				} );
			}

		}

	};

	/**
	 * Helper methods.
	 *
	 * @type {Object}
	 * @since 1.0.0
	 */
	const helpers = {

		/**
		 * Retreive custom image data from the image markup.
		 *
		 * @since 1.0.0
		 *
		 * @param  {Object} $element
		 *
		 * @return {array} Custom image data array.
		 */
		getImageCustomData: function( $element ) {
			let elementData = $element.data();
			let customData = [];

			for ( const [key, value] of Object.entries( elementData ) ) {
				if ( ! key.includes( 'lityCustom' ) ) {
					continue;
				}
				let split = value.split( ':' );
				customData.push( { class: key, element: split[0], content: split[1] } );
			}

			return customData;

		},

		/**
		 * Find an image object based on the image URL.
		 *
		 * @since 1.0.0
		 *
		 * @param  {string} Image src value.
		 *
		 * @return {array} Image object array.
		 */
		findImageObj: function( imageSrc ) {

			for ( var i = 0; i < lityMediaData.length; i++ ) {
				if ( lityMediaData[i].urls.includes( imageSrc ) ) {
					return lityMediaData[i];
				}
			}

			return {};

		}

	};

	$( document ).on( 'ready', lityScript.init );
	$( document ).on( 'ready', lityScript.fullSizeImages );
	$( document ).on( 'ready', lityScript.appendImageInfo );
	$( document ).on( 'lity:ready', lityScript.showImageInfo );
	$( document ).on( 'lity:open', lityScript.backgroundImages );
} )( jQuery );
