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
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

define( 'LITY_VERSION', '2.4.1' );
define( 'SLIMSELECT_VERSION', '1.27.1' );

if ( ! class_exists( 'WP_Lity' ) ) {

	/**
	 * Main Lity Class.
	 *
	 * @since 1.0.0
	 */
	final class WP_Lity {

		public function __construct() {

			require_once plugin_dir_path( __FILE__ ) . 'includes/class-settings.php';

			add_filter( 'wp_enqueue_scripts', [ $this, 'enqueue_lity' ], PHP_INT_MAX );

		}

		/**
		 * Enqueue Lity assets.
		 */
		public function enqueue_lity() {

			$options = ( new WP_Lity_Options )->get_lity_options();

			if ( in_array( get_the_ID(), $options['disabled_on'] ) ) {

				return;

			}

			$suffix = SCRIPT_DEBUG ? '' : '.min';

			// Style
			wp_enqueue_style( 'lity', plugin_dir_url( __FILE__ ) . "assets/css/lity/lity${suffix}.css", array(), LITY_VERSION, 'all' );

			$style = 'img[data-lity]:hover {
				cursor: pointer;
			}';

			wp_add_inline_style( 'lity', $style );

			// Script
			wp_enqueue_script( 'lity', plugin_dir_url( __FILE__ ) . "assets/js/lity/lity${suffix}.js", array( 'jquery' ), LITY_VERSION, true );

			$img_selectors = ! empty( $options['element_selectors'] ) ? $options['element_selectors'] : 'img';

			$script = "jQuery( document ).on( 'ready', function() {
				jQuery( '${img_selectors}' ).attr( 'data-lity', '' );
			} );";

			// Add an attribute to link to the full size image
			if ( 'yes' === $options['show_full_size'] ) {

				$script .= "jQuery( document ).on( 'ready', function() {
					jQuery( '${img_selectors}' ).each( function( img ) {
						let imgSrc = jQuery( this ).attr( 'src' );
						jQuery( this ).attr( 'data-lity-target', imgSrc.replace( /(?:[-_]?[0-9]+x[0-9]+)+/g, '' ) );
					} );
				} );";

			}

			wp_add_inline_script( 'lity', $script, 'after' );

		}

	}

}

new WP_Lity();
