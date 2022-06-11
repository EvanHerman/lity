<?php
/**
 * Plugin Name: Lity - Responsive Lightbox
 * Description: Ensure when an image is clicked in the post content it opens in a responsive lightbox.
 * Author: Evan Herman
 * Author URI: https://www.evan-herman.com
 * Version: 1.0.0
 * Text Domain: lity
 * Domain Path: /languages
 * Tested up to: 6.0
 *
 * @package Lity
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

define( 'LITY_VERSION', '2.4.1' );
define( 'LITY_SLIMSELECT_VERSION', '1.27.1' );

if ( ! class_exists( 'Lity' ) ) {

	/**
	 * Main Lity Class.
	 *
	 * @since 1.0.0
	 */
	final class Lity {

		/**
		 * Lity plugin constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_lity' ), PHP_INT_MAX );

			add_action( 'init', array( $this, 'lity_get_media' ), PHP_INT_MAX );

			add_action( 'wp_handle_upload', array( $this, 'clear_lity_media_transient' ), PHP_INT_MAX );

		}

		/**
		 * Enqueue Lity assets and create lightbox with inline script.
		 */
		public function enqueue_lity() {

			$options = ( new Lity_Options() )->get_lity_options();

			if ( in_array( get_the_ID(), $options['disabled_on'], false ) ) {

				return;

			}

			$suffix = SCRIPT_DEBUG ? '' : '.min';

			// Style.
			wp_enqueue_style( 'lity', plugin_dir_url( __FILE__ ) . "assets/css/lity/lity${suffix}.css", array(), LITY_VERSION, 'all' );

			$style = 'img[data-lity]:hover {
				cursor: pointer;
			}
			body.logged-in {
				top: 32px!important;
			}';

			// Script.
			wp_enqueue_script( 'lity', plugin_dir_url( __FILE__ ) . "assets/js/lity/lity${suffix}.js", array( 'jquery' ), LITY_VERSION, true );

			$img_selectors              = ! empty( $options['element_selectors'] ) ? $options['element_selectors'] : 'img';
			$excluded_element_selectors = $options['excluded_element_selectors'];

			$script = "jQuery( document ).on( 'ready', function() {
				jQuery( '${img_selectors}' ).not( '$excluded_element_selectors' ).attr( 'data-lity', '' );
			} );";

			// Add an attribute to link to the full size image.
			if ( 'yes' === $options['show_full_size'] ) {

				$script .= "jQuery( document ).on( 'ready', function() {
					jQuery( '${img_selectors}' ).each( function( img ) {
						let imgSrc = jQuery( this ).attr( 'src' );
						let fullsizeImgSrc = imgSrc.replace( /(?:[-_]?[0-9]+x[0-9]+)+/g, '' );

						// make lity lightboxes show full sized versions of the image
						jQuery( this ).attr( 'data-lity-target', fullsizeImgSrc );
					} );
				} );";

			}

			$media_data = get_transient( 'lity_media' );

			if ( false !== $media_data && 'yes' === $options['show_image_info'] ) {

				$style .= '.lity-content {
					display: inline-flex;
					align-items: center;
					justify-content: center;
				}
				.lity-info {
					background: #4c4c4c63;
					vertical-align: middle;
					display: -webkit-inline-flex;
					-webkit-box-orient: vertical;
					-webkit-box-direction: normal;
					-webkit-flex-direction: column;
					-webkit-box-pack: center;
					-webkit-flex-pack: center;
					-webkit-justify-content: center;
					-webkit-flex-align: center;
					-webkit-align-items: center;
					height: 100vh;
					max-width: 33%;
					padding: 0 2em 0 1em;
				}
				.lity-info > * {
					width: 100%;
					color: #fafafa;
				}
				.lity-info > h4 {
					text-decoration: underline;
				}';

				$script .= "jQuery( document ).on( 'ready', function() {
					jQuery( '${img_selectors}' ).each( function( img ) {
						let imgSrc = jQuery( this ).attr( 'src' );
						let fullsizeImgSrc = imgSrc.replace( /(?:[-_]?[0-9]+x[0-9]+)+/g, '' );

						let imgObj = ${media_data}.filter( media => media.url == fullsizeImgSrc );

						if ( imgObj.length ) {

							if ( !! imgObj[0].title ) {

								jQuery( this ).attr( 'data-lity-title', imgObj[0].title );

							}

							if ( !! imgObj[0].caption ) {

								jQuery( this ).attr( 'data-lity-description', imgObj[0].caption );

							}

							if ( !! imgObj[0].altText ) {

								jQuery( this ).attr( 'data-lity-alt-text', imgObj[0].altText );

							}
						}
					} );
				} );";

				$script .= "jQuery( document ).on( 'lity:ready', function( event, lightbox ) {
					const triggerElement = lightbox.opener();
					const altText = triggerElement.data( 'lity-alt-text' );
					const title = triggerElement.data( 'lity-title' );
					const description = triggerElement.data( 'lity-description' );

					if ( !! title || !! description ) {

						jQuery( '.lity-content' ).append( '<div class=lity-info></div>' );

					}

					if ( !! title ) {

						jQuery( '.lity-info' ).append( '<h4>' + triggerElement.data( 'lity-title' ) + '</h4>' );

					}

					if ( !! altText ) {

						jQuery( '.lity-info' ).append( '<p>' + triggerElement.data( 'lity-altText' ) + '</p>' );

					}

					if ( !! description ) {

						jQuery( '.lity-info' ).append( '<p>' + triggerElement.data( 'lity-description' ) + '</p>' );

					}

				} );";

			}

			wp_add_inline_style( 'lity', $style );
			wp_add_inline_script( 'lity', $script, 'after' );

		}

		/**
		 * Create a transient to track media data.
		 *
		 * @since 1.0.0
		 */
		public function lity_get_media() {

			delete_transient( 'lity_media' );

			if ( is_admin() || 'no' === ( new Lity_Options() )->get_lity_option( 'show_image_info' ) ) {

				return;

			}

			$media = get_transient( 'lity_media' );

			if ( false !== $media ) {

				return;

			}

			$media_query = new \WP_Query(
				array(
					'post_type'      => 'attachment',
					'posts_per_page' => '-1',
					'post_status'    => 'inherit',
				)
			);

			if ( ! $media_query->have_posts() ) {

				return;

			}

			$media = array();

			while ( $media_query->have_posts() ) {

				$media_query->the_post();

				$image_id  = get_the_ID();
				$image_src = wp_get_attachment_image_src( $image_id, 'full' );

				if ( false === $image_src || count( $image_src ) < 1 ) {

					continue;

				}

				$media[] = array(
					'url'     => $image_src[0],
					'title'   => get_the_title( $image_id ),
					'caption' => get_the_excerpt( $image_id ),
					'altText' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
				);

			}

			set_transient( 'lity_media', json_encode( $media ), WEEK_IN_SECONDS );

		}

		/**
		 * Clear the lity media transient when a new image is uploaded.
		 *
		 * @since 1.0.0
		 */
		public function clear_lity_media_transient() {

			delete_transient( 'lity_media' );

		}

	}

}

new Lity();
