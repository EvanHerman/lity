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

define( 'LITY_PLUGIN_VERSION', '1.0.0' );
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

			$options    = ( new Lity_Options() )->get_lity_options();
			$media_data = get_transient( 'lity_media' );

			if ( false === $media_data || in_array( get_the_ID(), $options['disabled_on'], false ) ) {

				return;

			}

			$suffix = SCRIPT_DEBUG ? '' : '.min';

			// Styles.
			wp_enqueue_style( 'lity', plugin_dir_url( __FILE__ ) . "assets/css/lity/lity${suffix}.css", array(), LITY_VERSION, 'all' );
			wp_enqueue_style( 'lity-styles', plugin_dir_url( __FILE__ ) . "assets/css/lity-styles.css", array( 'lity' ), LITY_PLUGIN_VERSION, 'all' );

			// Scripts.
			wp_enqueue_script( 'lity', plugin_dir_url( __FILE__ ) . "assets/js/lity/lity${suffix}.js", array( 'jquery' ), LITY_VERSION, true );
			wp_enqueue_script( 'lity-script', plugin_dir_url( __FILE__ ) . "assets/js/lity-script.js", array( 'lity' ), LITY_PLUGIN_VERSION, true );

			wp_localize_script(
				'lity-script',
				'lityScriptData',
				array(
					'options'      => $options,
					'imgSelectors' => ! empty( $options['element_selectors'] ) ? $options['element_selectors'] : 'img',
					'mediaData'    => get_transient( 'lity_media' ),
				)
			);

		}

		/**
		 * Create a transient to track media data.
		 *
		 * @since 1.0.0
		 */
		public function lity_get_media() {

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

				global $_wp_additional_image_sizes;

				$image_urls = array();

				foreach ( array_keys( $_wp_additional_image_sizes ) as $image_size ) {

					$image_urls[] = wp_get_attachment_image_url( $image_id, $image_size );

				}

				// Ensure 'full' image size is first in the array.
				array_unshift( $image_urls, wp_get_attachment_image_url( $image_id, 'full' ) );

				$media[] = array(
					'urls'    => array_values( array_unique( $image_urls ) ),
					'title'   => get_the_title( $image_id ),
					'caption' => get_the_excerpt( $image_id ),
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
