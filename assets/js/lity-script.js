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

			if ( 'no' === lityScriptData.options.show_full_size ) {
				return;
			}

			$( lityScriptData.imgSelectors ).each( function( img ) {
				if ( ! lityMediaData ) {
					return;
				}

				let imgSrc = $( this ).attr( 'src' );
				let imgObj = [];

				lityMediaData.forEach( ( media, index ) => {
					if ( media.urls.includes( imgSrc ) ) {
						imgObj.push( lityMediaData[ index ] );
					}
				} );

				if ( imgObj.length ) {
					// make lity lightboxes show full sized versions of the image
					$( this ).attr( 'data-lity-target', imgObj[0].urls[0] );
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

			$( lityScriptData.imgSelectors ).each( function( img ) {
				let imgSrc = $( this ).attr( 'src' );

				let imgObj = [];

				lityMediaData.forEach( ( media, index ) => {
					if ( media.urls.includes( imgSrc ) ) {
						imgObj.push( lityMediaData[ index ] );
					}
				} );

				if ( imgObj.length ) {

					if ( !! imgObj[0].title ) {
						$( this ).attr( 'data-lity-title', imgObj[0].title );
					}

					if ( !! imgObj[0].caption ) {
						$( this ).attr( 'data-lity-description', imgObj[0].caption );
					}

					if ( !! imgObj[0].custom_data && Object.keys( imgObj[0].custom_data ).length ) {

						for (const [ key, value ] of Object.entries( imgObj[0].custom_data )) {
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

		}

	};

	$( document ).on( 'ready', lityScript.init );
	$( document ).on( 'ready', lityScript.fullSizeImages );
	$( document ).on( 'ready', lityScript.appendImageInfo );
	$( document ).on( 'lity:ready', lityScript.showImageInfo );
} )( jQuery );
