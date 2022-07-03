<?php

class Test_Lity extends WP_UnitTestCase {

	/**
	 * Media data var.
	 *
	 * @var string
	 */
	private $media_data;

	private $default_options;

	function setUp(): void {

		parent::setUp();

		$this->media_data = '[{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-1536x1044.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-600x408.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-100x100.jpg"],"title":"Image 5","caption":"Image description"},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-1097x1536.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-600x840.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-100x100.jpg"],"title":"Image 4","caption":"hello"},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-1097x1536.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-600x840.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-100x100.jpg"],"title":"Image 3","caption":"Image description"},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-1097x1536.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-600x840.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-100x100.jpg"],"title":"Image 2","caption":"This is the image caption."},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-1536x1044.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-600x408.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-100x100.jpg"],"title":"","caption":""}]';

		$this->default_options = array(
			'show_full_size'             => 'yes',
			'use_background_image'       => 'yes',
			'show_image_info'            => 'no',
			'disabled_on'                => array(),
			'element_selectors'          => 'img',
			'excluded_element_selectors' => '',
		);

		require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/lity.php';

	}

	function tearDown(): void {

		parent::tearDown();

	}

	/**
	 * Test that the constants are defined.
	 *
	 * @since 1.0.0
	 */
	function testConstants() {

		$this->assertEquals(
			[
				'LITY_PLUGIN_VERSION'     => true,
				'LITY_VERSION'            => true,
				'LITY_SLIMSELECT_VERSION' => true,
				'LITY_TAGIFY_VERSION'     => true,
			],
			[
				'LITY_PLUGIN_VERSION'     => defined( 'LITY_PLUGIN_VERSION' ),
				'LITY_VERSION'            => defined( 'LITY_VERSION' ),
				'LITY_SLIMSELECT_VERSION' => defined( 'LITY_SLIMSELECT_VERSION' ),
				'LITY_TAGIFY_VERSION'     => defined( 'LITY_TAGIFY_VERSION' ),
			]
		);

	}

	/**
	 * Test that the lity class is available.
	 *
	 * @since 1.0.0
	 */
	function testClassExists() {

		$this->assertTrue( class_exists( 'Lity' ) );

	}

	/**
	 * Test the assets do not enqueue when there is no lity_media transient data.
	 */
	function testEnqueueLityNoMediaData() {

		( new Lity() )->enqueue_lity();

		$this->assertTrue( ! wp_script_is( 'lity' ) );

	}

	/**
	 * Test that lity.js is enqueued.
	 */
	function testEnqueueLity() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->enqueue_lity();

		global $wp_scripts;

		$this->assertTrue( in_array( 'lity', $wp_scripts->queue ) );

	}

	/**
	 * Test that lity-script.js is enqueued.
	 */
	function testEnqueueLityScript() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->enqueue_lity();

		global $wp_scripts;

		$this->assertTrue( in_array( 'lity-script', $wp_scripts->queue ) );

	}

	/**
	 * Test the lity-script.js dependencies are correct.
	 */
	function testEnqueueLityScriptDependencies() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->enqueue_lity();

		global $wp_scripts;

		$this->assertTrue(
			in_array( 'lity', $wp_scripts->registered['lity-script']->deps ),
			'lity is not set as a dependency of lity-script.'
		);

	}

	/**
	 * Test the lity-script.js localized data.
	 */
	function testEnqueueLityScriptLocalizedData() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->enqueue_lity();

		global $wp_scripts;

		$expected = 'var lityScriptData = {"options":{"show_full_size":"yes","use_background_image":"yes","show_image_info":"no","disabled_on":[],"element_selectors":"img","excluded_element_selectors":""},"element_selectors":"img","excluded_element_selectors":"","mediaData":';

		$this->assertTrue(
			strpos( $wp_scripts->registered['lity-script']->extra['data'], $expected ) !== false,
			"The lity-script localized data doesn't appear to be correct."
		);

	}

	/**
	 * Test that lity.css is enqueued.
	 */
	function testEnqueueLityStyle() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->enqueue_lity();

		global $wp_styles;

		$this->assertArrayHasKey(
			'lity',
			$wp_styles->registered,
			'The lity script is not enqueued.'
		);

	}

	/**
	 * Test that lity-styles.css is enqueued.
	 */
	function testEnqueueLityStylesStyle() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->enqueue_lity();

		global $wp_styles;

		$this->assertArrayHasKey(
			'lity-styles',
			$wp_styles->registered,
			'The lity-styles script is not enqueued.'
		);

	}

	/**
	 * Test that the lity_media is not set when show_full_size is set to 'no'.
	 */
	function testLityGetMediaNotSetShowFullSizeNo() {

		delete_transient( 'lity_media' );

		$options = ( new Lity() )->default_options;

		$options['show_full_size'] = 'no';

		update_option( 'lity_options', $options );

		( new Lity() )->set_media_transient();

		$this->assertFalse(
			get_transient( 'lity_media' ),
			"The lity_media transient is being set when it shouldn't be."
		);

	}

	/**
	 * Test that the lity_media returns early when already set.
	 */
	function testLityGetMediaReturnsWhenSet() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->set_media_transient();

		$this->assertEquals( get_transient( 'lity_media' ), $this->media_data );

	}

	/**
	 * Test that the lity_media returns early when no media attachments are uploaded to the site.
	 */
	function testLityGetMediaReturnsWhenNoAttachmentsOnSite() {

		( new Lity() )->set_media_transient();

		$this->assertFalse( get_transient( 'lity_media' ) );

	}

	/**
	 * Test that the lity_media transient returns early when no $image_src is found.
	 */
	function testLityGetMediaReturnsWhenNoImgSRC() {

		$image_id = $this->factory->post->create(
			[
				'post_type'    => 'attachment',
				'post_title'   => 'Image #1',
				'post_excerpt' => 'Image excerpt, used for captions',
				'post_status'  => 'inherit',
			]
		);

		( new Lity() )->set_media_transient();

		$this->assertEquals(
			get_transient( 'lity_media' ),
			'[]',
			'The lity_media transient does not match the expected empty json string.'
		);

	}

	/**
	 * Test that the lity_media transient is set.
	 */
	function testLityGetMedia() {

		$image_id = $this->factory->post->create(
			[
				'post_type'    => 'attachment',
				'post_title'   => 'Image #1',
				'post_excerpt' => 'Image excerpt, used for captions',
				'post_status'  => 'inherit',
			]
		);

		add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size, $icon ) {
			return array( 'http://example.org/wp-content/uploads/2022/06/image-1.jpg', '1800', '1224' );
		}, 10, 4 );

		( new Lity() )->set_media_transient();

		$expected = '[{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1.jpg"],"title":"Image #1","caption":"Image excerpt, used for captions","custom_data":[]}]';

		$this->assertEquals(
			get_transient( 'lity_media' ),
			$expected,
			'The lity_media transient does not match the expected value.'
		);

	}

	/**
	 * Test that clear_lity_media_transient() clears the transient data as intended.
	 */
	function testClearLityMediaTransient() {

		set_transient( 'lity_media', $this->media_data );

		( new Lity() )->clear_lity_media_transient();

		$this->assertFalse(
			get_transient( 'lity_media' ),
			'The lity_media transient is still set, even though it was cleared.'
		);

	}

}
