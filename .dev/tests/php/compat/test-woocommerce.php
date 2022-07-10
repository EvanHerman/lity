<?php

class Test_Lity_WooCommerce extends WP_UnitTestCase {

	/**
	 * Lity_WooCommerce Class Instance
	 */
	private $woocommerce;

	function setUp(): void {

		parent::setUp();

		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

		update_option( 'active_plugins', array( 'woocommerce/woocommerce.php' ) );

		update_option( 'template', array( 'twentytwentyone' ) );
		update_option( 'stylesheet', array( 'twentytwentyone' ) );

		// $this->woocommerce = new Lity_WooCommerce();

	}

	function tearDown(): void {

		parent::tearDown();

	}

	/**
	 * Test that when WooCommerce is active, the exclusions are filtered in.
	 */
	public function testWoocommerceExclusions() {

		$woocommerce_exclusions = apply_filters( 'lity_excluded_element_selectors', array() );

		$lity = new Lity();

		$this->assertEquals(
			array(
				'li.type-product .attachment-woocommerce_thumbnail',
				'#wpadminbar img',
			),
			$woocommerce_exclusions
		);

	}

	/**
	 * Test that when the Storefront theme is active, the exclusions are filtered in.
	 */
	public function testStorefrontExclusions() {

		$this->markTestSkipped( 'Revisit after we install the Storefront theme in the CI/CD pipeline.' );

		$woocommerce_exclusions = apply_filters( 'lity_excluded_element_selectors', array() );

		$lity = new Lity();

		$this->assertEquals(
			array(
				'li.type-product .attachment-woocommerce_thumbnail',
				'#wpadminbar img',
			),
			$woocommerce_exclusions
		);

	}

}
