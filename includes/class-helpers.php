<?php
/**
 * Lity Helpers Class
 *
 * @package Lity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

if ( ! class_exists( 'Lity_Helpers' ) ) {

	/**
	 * Lity Helpers Class.
	 *
	 * @since 1.0.0
	 */
	final class Lity_Helpers {

		/**
		 * Remove element selectors from Lity.
		 *
		 * @param array        $value     Lity options array.
		 * @param string|array $selectors Element selector string or array of selectors to exclude from Lity.
		 *
		 * @return array Filtered lity_options with our custom excluded selectors added.
		 */
		public function add_selector_exclusion( $value, $selectors ) {

			// Return the options early so our exclusions don't show up on the settings page.
			if ( is_admin() ) {

				return $value;

			}

			$excluded_element_selectors = json_decode( $value['excluded_element_selectors'], true );

			if ( is_array( $selectors ) ) {

				foreach ( $selectors as $selector ) {

					$excluded_element_selectors[] = array(
						'value' => $selector,
					);

				}
			}

			if ( is_string( $selectors ) ) {

				$excluded_element_selectors[] = array(
					'value' => $selectors,
				);

			}

			$value['excluded_element_selectors'] = json_encode( $excluded_element_selectors );

			return $value;

		}

		/**
		 * Retreive the number of images on the site.
		 *
		 * @return integer Number of images on the site.
		 */
		public function get_site_image_count() {

			$attachment_count = wp_count_attachments( 'image' );

			unset( $attachment_count->trash );

			return (int) array_sum( (array) $attachment_count );

		}

	}

}
