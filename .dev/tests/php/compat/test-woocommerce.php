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

	}

	function tearDown(): void {

		parent::tearDown();

		update_option( 'active_plugins', array( 'woocommerce/woocommerce.php' ) );

		switch_theme( 'twentytwentytwo' );

	}

	/**
	 * Test that when WooCommerce is active, the exclusions are filtered in.
	 */
	public function testWoocommerceExclusions() {

		$this->woocommerce = new Lity_WooCommerce();

		$lity = new Lity();

		$woocommerce_exclusions = $this->woocommerce->woocommerce_exclusions( array() );

		$this->assertEquals(
			array(
				'li.type-product .attachment-woocommerce_thumbnail',
			),
			array_unique( $woocommerce_exclusions )
		);

	}

	/**
	 * Test that when the Storefront theme is active, the exclusions are filtered in.
	 */
	public function testStorefrontExclusions() {

		$woocommerce_exclusions = apply_filters( 'lity_excluded_element_selectors', array() );

		switch_theme( 'storefront' );

		$lity = new Lity();

		$this->assertEquals(
			array(
				'li.type-product .attachment-woocommerce_thumbnail',
				'.storefront-product-pagination img',
				'#wpadminbar img',
			),
			$woocommerce_exclusions
		);

		switch_theme( 'twentytwentytwo' );

	}

}
