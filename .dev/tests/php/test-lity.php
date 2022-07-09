<?php

class Test_Lity extends WP_UnitTestCase {

	/**
	 * Media data var.
	 *
	 * @var string
	 */
	private $media_data;

	/**
	 * Lity class instance.
	 */
	private $lity;

	/**
	 * Helpers class instance.
	 */
	private $helpers;

	function setUp(): void {

		parent::setUp();

		$this->media_data = '[{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-1536x1044.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-600x408.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-5-100x100.jpg"],"title":"Image 5","caption":"Image description"},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-1097x1536.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-600x840.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-4-100x100.jpg"],"title":"Image 4","caption":"hello"},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-1097x1536.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-600x840.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-3-100x100.jpg"],"title":"Image 3","caption":"Image description"},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-1097x1536.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-600x840.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-2-100x100.jpg"],"title":"Image 2","caption":"This is the image caption."},{"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-1536x1044.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-300x300.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-600x408.jpg","http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1-100x100.jpg"],"title":"","caption":""}]';

		require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/lity.php';

		$this->lity = new Lity();

		$this->helpers = new Lity_Helpers();

		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

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
	 * Test the lity_options option is created on plugin activation.
	 */
	function testPluginActivation() {

		delete_option( 'lity_options' );

		$this->lity->plugin_activation();

		$this->assertNotEmpty( get_option( 'lity_options', false ) );

	}

	/**
	 * Test the custom plugin 'Settings' links exist.
	 */
	function testPluginActionLinks() {

		$options = new Lity_Options(array());

		$options->register_menu_item();

		// $this->assertTrue( is_admin() );

		$this->assertEquals(
			[
				'<a href="http://example.org/wp-admin/options-general.php?page=lity-options" aria-label="Lity - Responsive Lightboxes Settings">Settings</a>',
			],
			$this->lity->custom_plugin_action_links( [] )
		);

	}

	/**
	 * Test the assets do not enqueue when there is no lity_media transient data.
	 */
	function testEnqueueLityNoMediaData() {

		$this->lity->enqueue_lity();

		$this->assertTrue( ! wp_script_is( 'lity' ) );

	}

	/**
	 * Test that lity.js does not enqueue when the lity_is_disabled is true.
	 */
	function testEnqueueLityDisabled() {

		add_filter( 'lity_is_disabled', '__return_true' );

		set_transient( 'lity_media', $this->media_data );

		$this->lity->enqueue_lity();

		global $wp_scripts;

		$this->assertTrue( ! in_array( 'lity', $wp_scripts->queue ) );

	}

	/**
	 * Test that lity.js is enqueued.
	 */
	function testEnqueueLity() {

		set_transient( 'lity_media', $this->media_data );

		$this->lity->enqueue_lity();

		global $wp_scripts;

		$this->assertTrue( in_array( 'lity', $wp_scripts->queue ) );

	}

	/**
	 * Test that lity-script.js is enqueued.
	 */
	function testEnqueueLityScript() {

		set_transient( 'lity_media', $this->media_data );

		$this->lity->enqueue_lity();

		global $wp_scripts;

		$this->assertTrue( in_array( 'lity-script', $wp_scripts->queue ) );

	}

	/**
	 * Test the lity-script.js dependencies are correct.
	 */
	function testEnqueueLityScriptDependencies() {

		set_transient( 'lity_media', $this->media_data );

		$this->lity->enqueue_lity();

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

		$this->lity->enqueue_lity();

		global $wp_scripts;

		$expected = 'var lityScriptData = {"options":{"show_full_size":"yes","use_background_image":"yes","show_image_info":"no","caption_type":"caption","disabled_on":[],"element_selectors":"[{\"value\":\"img\"}]","excluded_element_selectors":"[]","generating_transient":false},"element_selectors":"img","excluded_element_selectors":"#wpadminbar img","mediaData":';

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

		$this->lity->enqueue_lity();

		$wp_styles = wp_styles();

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

		$this->lity->enqueue_lity();

		$wp_styles = wp_styles();

		$this->assertArrayHasKey(
			'lity-styles',
			$wp_styles->registered,
			'The lity-styles script is not enqueued.'
		);

	}

	/**
	 * Test that lity_media isn't scheduled when it's already running.
	 */
	function testScheduleLityMediaNotRunTwice() {

		update_option(
			'lity_options',
			wp_parse_args(
				array(
					'generating_transient' => true,
				),
				get_option( 'lity_options' )
			)
		);

		$this->lity->schedule_lity_media();

		$this->assertTrue( ! as_has_scheduled_action( 'lity_generate_media' ) );

		update_option(
			'lity_options',
			wp_parse_args(
				array(
					'generating_transient' => false,
				),
				get_option( 'lity_options' )
			)
		);

	}

	/**
	 * Test that lity_media isn't scheduled when the transient already exists.
	 */
	function testScheduleLityMediaNotRunWhenTransientAlreadySet() {

		set_transient( 'lity_media', $this->media_data );

		$this->lity->schedule_lity_media();

		$this->assertTrue( ! as_has_scheduled_action( 'lity_generate_media' ) );

	}

	/**
	 * Test that lity_media isn't scheduled when no images exist on the site.
	 */
	function testScheduleLityMediaNotRunWhenNoImages() {

		delete_transient( 'lity_media' );

		$this->lity->schedule_lity_media();

		$this->assertTrue( ! as_has_scheduled_action( 'lity_generate_media' ) );

	}

	/**
	 * Test that lity_media is scheduled when images exist on the site.
	 */
	function testScheduleLityMediaRunsWhenImages() {

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );

		delete_transient( 'lity_media' );

		$this->lity->schedule_lity_media();

		$this->assertTrue( as_has_scheduled_action( 'lity_generate_media' ) );

	}

	/**
	 * Test that multiple lity_media tasks are scheduled when multiple images exist on the site.
	 * Note: We filter per_page to 1, and upload 2 images. So this task should be scheduled twice.
	 */
	function testScheduleLityMediaRunsWhenMultipleImages() {

		add_filter( 'lity_transient_image_query_count', function( $per_page ) {
			return 1;
		} );

		$lity = new Lity();

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );

		delete_transient( 'lity_media' );

		$lity->schedule_lity_media();

		$this->assertEquals( 2, count( as_get_scheduled_actions( array( 'hook' => 'lity_generate_media' ), ARRAY_A ) ) );

	}

	/**
	 * Test that the transient is set when only one image exists, and no transient is set yet.
	 *
	 * - One image
	 * - Check `generating_transient` option is set to false
	 * - Check the transient is set
	 */
	function testLityTransientSetOneImage() {

		$image_id = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		delete_transient( 'lity_media' );

		$lity = new Lity();

		$lity->set_media_transient( 1 );

		$option = get_option( 'lity_options' );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertFalse( $option['generating_transient'] );
		$this->assertEquals( 1, count( $transient ) );
		$this->assertEquals( $image_id, $transient[0]['id'] );

	}

	/**
	 * Test that the transient is set when five images exists, and no transient is set yet.
	 *
	 * - Two images
	 * - Check `generating_transient` option is set to false
	 * - Check the transient is set and its length is 5
	 * - Filtering lity_transient_image_query_count to 1 image per page, so the query runs 5 times and 5 tasks are scheduled.
	 */
	function testLityTransientSetTwoImages() {

		add_filter( 'lity_transient_image_query_count', function( $per_page ) {
			return 1;
		} );

		$lity = new Lity();

		$x = 1;

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );

		delete_transient( 'lity_media' );

		while( $x <= 5 ) {
			$lity->set_media_transient( $x );
			$x++;
		}

		$option = get_option( 'lity_options' );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertFalse( $option['generating_transient'] );
		$this->assertEquals( 5, count( $transient ) );

	}

	/**
	 * Test that the custom data returns in the array as intended.
	 */
	function testLityTransientSetCustomData() {

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );

		delete_transient( 'lity_media' );

		$lity = new Lity();

		add_filter( 'lity_image_info_custom_data', function( $image_id ) {
			return array(
				'custom-class' => array(
					'element_wrap' => 'p',
					'content'      => 'Custom Content'
				),
			);
		} );

		$lity->set_media_transient( 1 );

		$option = get_option( 'lity_options' );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertFalse( $option['generating_transient'] );
		$this->assertTrue( array_key_exists( 'custom-class', $transient[0]['custom_data'] ) );
		$this->assertEquals( 'p', $transient[0]['custom_data']['custom-class']['element_wrap'] );
		$this->assertEquals( 'Custom Content', $transient[0]['custom_data']['custom-class']['content'] );

	}

	/**
	 * Test that the custom data is not set in the transient when content key is empty.
	 */
	function testLityTransientSetCustomDataNoContent() {

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );

		delete_transient( 'lity_media' );

		$lity = new Lity();

		add_filter( 'lity_image_info_custom_data', function( $image_id ) {
			return array(
				'custom-class' => array(
					'element_wrap' => 'p',
					'content'      => ''
				),
			);
		} );

		$lity->set_media_transient( 1 );

		$option = get_option( 'lity_options' );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertFalse( $option['generating_transient'] );
		$this->assertFalse( array_key_exists( 'custom-class', $transient[0]['custom_data'] ) );

	}

	/**
	 * Test that transient is not updated when an image is updated and no transient is set.
	 */
	function testLityUpdateLityMediaTransientNoTransientSet() {

		$image_id = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		$lity = new Lity();

		$lity->set_media_transient( 1 );

		$args = array(
			'ID'           => $image_id,
			'post_title'   => 'Custom Image Title',
			'post_excerpt' => 'My custom caption...',
			'post_content' => 'My custom description...',
		);

		delete_transient( 'lity_media' );

		wp_update_post( $args );

		$this->assertFalse( get_transient( 'lity_media' ) );

	}

	/**
	 * Test that transient does not update when the attachment doesn't exist in the transient.
	 */
	function testLityUpdateLityMediaTransientAttachmentNotFound() {

		$lity = new Lity();

		media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png' );
		$image_id = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		delete_transient( 'lity_media' );

		$lity->set_media_transient( 1 );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$attachment_index = array_search( $image_id, array_column( $transient, 'id' ), true );

		if ( false !== $attachment_index ) {

			unset( $transient[ $attachment_index ] );

			$transient = array_values( $transient );

		}

		set_transient( 'lity_media', json_encode( $transient ) );

		$args = array(
			'ID'           => $image_id,
			'post_title'   => 'Custom Image Title',
			'post_excerpt' => 'My custom caption...',
			'post_content' => 'My custom description...',
		);

		wp_update_post( $args );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$ids = wp_list_pluck( $transient, 'id' );

		$this->assertFalse( in_array( $image_id, $ids ) );

	}

	/**
	 * Test that transient is updated correctly when an attachment is updated.
	 */
	function testLityUpdateLityMediaTransient() {

		$image_id = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		$lity = new Lity();

		$lity->set_media_transient( 1 );

		$args = array(
			'ID'           => $image_id,
			'post_title'   => 'Custom Image Title',
			'post_excerpt' => 'My custom caption...',
			'post_content' => 'My custom description...',
		);

		wp_update_post( $args );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertEquals( $image_id, $transient[0]['id'] );
		$this->assertEquals( 'Custom Image Title', $transient[0]['title'] );
		$this->assertEquals( 'My custom caption...', $transient[0]['caption'] );
		$this->assertEquals( 'My custom description...', $transient[0]['description'] );

	}

	/**
	 * Test that transient does not update when the attachment doesn't exist in the transient.
	 */
	function testLityHandleNewImageNonImage() {

		$lity = new Lity();

		$lity->lity_handle_new_image( [], 123 );

		delete_transient( 'lity_media' );

		$lity->set_media_transient( 1 );

		$this->assertFalse( get_transient( 'lity_media' ) );

	}

	/**
	 * Test that transient does not update when the attachment doesn't exist in the transient.
	 */
	function testLityRemoveImageFromTransient() {

		$image_id = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		$lity = new Lity();

		delete_transient( 'lity_media' );

		$lity->set_media_transient( 1 );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertEquals( $image_id, $transient[0]['id'] );

		$lity->lity_remove_image_from_transient( $image_id );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertTrue( empty( $transient ) );

	}

	/**
	 * Test that transient does not remove anything when the passed in ID is not an attachment.
	 */
	function testLityRemoveImageFromTransientNonAttachment() {

		$lity = new Lity();

		$post_id = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'Post #1',
				'post_content' => 'Post description',
				'post_excerpt' => 'Post excerpt',
				'post_status'  => 'inherit',
			]
		);

		$transient_data = array(
			array(
				'id'          => $post_id,
				'urls'        => array(),
				'title'       => 'Post #1',
				'caption'     => '',
				'description' => '',
				'custom_data' => array(),
			)
		);

		// Add a non-attachment to the transient
		set_transient( 'lity_media', json_encode( $transient_data ) );

		$lity->lity_remove_image_from_transient( $post_id );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$this->assertEquals( $post_id, $transient[0]['id'] );

	}

	/**
	 * Test that transient does not do anything when the transient doesn't exist.
	 */
	function testLityRemoveImageFromTransientNoTransient() {

		$lity = new Lity();

		$image_id = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		delete_transient( 'lity_media' );

		$lity->lity_remove_image_from_transient( $image_id );

		$this->assertFalse( get_transient( 'lity_media' ) );

	}

	/**
	 * Test that transient does not do anything when the transient doesn't exist.
	 */
	function testLityRemoveImageFromTransientNoAttachmentIndex() {

		$lity = new Lity();

		$image_id_1 = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );
		$image_id_2 = media_sideload_image( 'https://s.w.org/style/images/wp-header-logo.png', null, null, 'id' );

		delete_transient( 'lity_media' );

		$lity->set_media_transient( 1 );

		$transient = json_decode( get_transient( 'lity_media' ), true );

		$attachment_index = array_search( $image_id_1, array_column( $transient, 'id' ), true );

		if ( false !== $attachment_index ) {

			unset( $transient[ $attachment_index ] );

			$transient = array_values( $transient );

		}

		set_transient( 'lity_media', json_encode( $transient ) );

		$lity->lity_remove_image_from_transient( $image_id_1 );

		$this->assertEquals( $image_id_2, $transient[0]['id'] );

	}

	/**
	 * Test that the generating cache notice doens't display when the lity_options['generating_transient'] option is false.
	 */
	function testLityDisplayTransientGeneratingNoticeNull() {

		$lity = new Lity();

		update_option(
			'lity_options',
			wp_parse_args(
				array(
					'generating_transient' => false,
				),
				get_option( 'lity_options' )
			)
		);

		$this->assertNull( $lity->display_generating_transient_notice() );

	}

	/**
	 * Test that the generating cache notice displays when the lity_options['generating_transient'] option is true.
	 */
	function testLityDisplayTransientGeneratingNotice() {

		$lity = new Lity();

		update_option(
			'lity_options',
			wp_parse_args(
				array(
					'generating_transient' => true,
				),
				get_option( 'lity_options' )
			)
		);

		$this->expectOutputRegex( '/Lity - Responsive Lightboxes is fetching your image metadata and caching a few things to improve performance. This all happens in the background. This notice will disappear when the process is complete./' );

		$this->assertNull( $lity->display_generating_transient_notice() );

	}

	/**
	 * Test that the lity_media is not set when show_full_size is set to 'no'.
	 */
	function testLityGetMediaNotSetShowFullSizeNo() {

		delete_transient( 'lity_media' );

		$options = $this->lity->default_options;

		$options['show_full_size'] = 'no';

		update_option( 'lity_options', $options );

		$this->lity->set_media_transient( 1 );

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

		$this->lity->set_media_transient( 1 );

		$this->assertEquals( get_transient( 'lity_media' ), $this->media_data );

	}

	/**
	 * Test that the lity_media returns early when no media attachments are uploaded to the site.
	 */
	function testLityGetMediaReturnsWhenNoAttachmentsOnSite() {

		$this->lity->set_media_transient( 1 );

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

		$this->lity->set_media_transient( 1 );

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
				'post_content' => 'Image description, used for captions',
				'post_excerpt' => 'Image excerpt, used for captions',
				'post_status'  => 'inherit',
			]
		);

		add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size, $icon ) {
			return array( 'http://example.org/wp-content/uploads/2022/06/image-1.jpg', '1800', '1224' );
		}, 10, 4 );

		$this->lity->set_media_transient( 1 );

		$expected = '[{"id":'.$image_id.',"urls":["http:\/\/example.org\/wp-content\/uploads\/2022\/06\/image-1.jpg"],"title":"Image #1","caption":"Image excerpt, used for captions","description":"Image description, used for captions","custom_data":[]}]';

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

		$this->lity->clear_lity_media_transient();

		$this->assertFalse(
			get_transient( 'lity_media' ),
			'The lity_media transient is still set, even though it was cleared.'
		);

	}

}
