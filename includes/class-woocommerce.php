<?php
/**
 * Lity Options Class
 *
 * @package Lity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

if ( ! class_exists( 'Lity_WooCommerce' ) ) {

	/**
	 * Lity Options Class.
	 *
	 * @since 1.0.0
	 */
	final class Lity_WooCommerce {

		/**
		 * Lity options class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			if ( ! function_exists( 'is_plugin_active' ) ) {

				include_once ABSPATH . 'wp-admin/includes/plugin.php';

			}

			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

				return;

			}

			add_filter( 'option_lity_options', array( $this, 'woocommerce_exclusions' ), PHP_INT_MAX, 2 );

		}

		/**
		 * Remove specific WooCommerce elements from opening in a lightbox.
		 *
		 * @param array $value lity_options value.
		 *
		 * @return array Filtered lity_options value.
		 */
		public function woocommerce_exclusions( $value ) {

			if ( is_admin() ) {

				return $value;

			}

			$value['excluded_element_selectors'] .= ',li.type-product .attachment-woocommerce_thumbnail';

			return $value;

		}

	}

}

new Lity_WooCommerce();
