<?php

class Test_Lity_Options extends WP_UnitTestCase {

	/**
	 * Options Class Instance
	 */
	private $settings;

	function setUp(): void {

		parent::setUp();

		$lity = new Lity();

		$this->settings = new Lity_Options( $lity->default_options );

	}

	function tearDown(): void {

		parent::tearDown();

		// Delete all attachments that were uploaded in any tests.
		$media_query = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 100,
				'post_status'    => 'inherit',
			)
		);

		if ( $media_query->have_posts() ) {

			while ( $media_query->have_posts() ) {

				$media_query->the_post();

				wp_delete_post( get_the_ID(), true );

			}

		}

	}

	public function testTest() {

		$this->assertTrue( true );

	}

}
