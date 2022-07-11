<?php

class Test_Lity_Helpers extends WP_UnitTestCase {

	/**
	 * Helpers Class Instance
	 */
	private $helpers;

	function setUp(): void {

		parent::setUp();

		$this->helpers = new Lity_Helpers();

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

	/**
	 * Test that the image count returns the expected value.
	 */
	public function testGetSiteImageCount() {

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );

		$this->assertEquals( 8, $this->helpers->get_site_image_count() );

	}

}
