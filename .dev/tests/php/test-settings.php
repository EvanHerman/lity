<?php

class Test_Lity_Options extends WP_UnitTestCase {

	/**
	 * Lity Class Instance
	 */
	private $lity;

	/**
	 * Options Class Instance
	 */
	private $settings;

	function setUp(): void {

		parent::setUp();

		wp_set_current_user( self::factory()->user->create( [
			'role' => 'administrator',
		] ) );

		$this->lity = new Lity();

		$this->settings = new Lity_Options( $this->lity->default_options );

		$_GET = null;

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
	 * Test the removable query args are added.
	 */
	public function testRemovableQueryArgs() {

		$this->assertEquals(
			[
				'lity-transient-cleared',
				'lity-settings-reset',
			],
			$this->settings->removable_query_args( array() )
		);

	}

	/**
	 * Test the plugin menu items are registered.
	 */
	public function testMenuItemsRegistered() {

		$this->settings->register_menu_item();

		$this->assertEquals(
			'http://example.org/wp-admin/options-general.php?page=lity-options',
			menu_page_url( 'lity-options', false )
		);

	}

	/**
	 * Test the settings page styles are enqueued.
	 */
	public function testMenuStyles() {

		$this->settings->enqueue_settings_styles();

		$wp_styles = wp_styles();

		$this->assertEquals(
			[
				'slimselect' => true,
				'tagify'     => true,
			],
			[
				'slimselect' => array_key_exists( 'slimselect', $wp_styles->registered ),
				'tagify'     => array_key_exists( 'tagify', $wp_styles->registered ),
			]
		);

	}

	/**
	 * Test the settings page scripts are enqueued.
	 */
	public function testMenuScripts() {

		$this->settings->enqueue_settings_scripts();

		global $wp_scripts;

		$this->assertEquals(
			[
				'slimselect' => true,
				'tagify'     => true,
			],
			[
				'slimselect' => in_array( 'slimselect', $wp_scripts->queue, true ),
				'tagify'     => in_array( 'tagify', $wp_scripts->queue, true ),
			]
		);

	}

	/**
	 * Test the get_lity_options() method.
	 */
	public function testGetLityOptions() {

		$this->assertEquals(
			$this->settings->default_options,
			$this->settings->get_lity_options()
		);

	}

	/**
	 * Test that the get_lity_option() method returns an empty string when the option value doesn't exist
	 */
	public function testGetLityOptionNonExistentOption() {

		$this->assertEmpty( $this->settings->get_lity_option( 'doesnt_exist' ) );

	}

	/**
	 * Test that the get_lity_option() method returns the correct value.
	 */
	public function testGetLityOption() {

		update_option(
			'lity_options',
			wp_parse_args(
				array(
					'show_full_size'       => 'off',
					'use_background_image' => 'off',
					'show_image_info'      => 'off',
					'caption_type'         => 'description',
				),
				get_option( 'lity_options' )
			)
		);

		$this->assertEquals(
			[
				'show_full_size'       => 'off',
				'use_background_image' => 'off',
				'show_image_info'      => 'off',
				'caption_type'         => 'description',
			],
			[
				'show_full_size'       => $this->settings->get_lity_option( 'show_full_size' ),
				'use_background_image' => $this->settings->get_lity_option( 'use_background_image' ),
				'show_image_info'      => $this->settings->get_lity_option( 'show_image_info' ),
				'caption_type'         => $this->settings->get_lity_option( 'caption_type' ),
			]
		);

	}

	/**
	 * Test that the update_lity_option() updates the lity_option value as expected.
	 */
	public function testUpdateLityOption() {

		$this->settings->update_lity_option( array() );

		$this->assertEquals(
			$this->settings->default_options,
			$this->settings->get_lity_options()
		);

		$this->settings->update_lity_option( array( 'new_value' => 'test_value' ) );

		$this->assertEquals(
			array_merge( $this->settings->get_lity_options(), array( 'new_value' => 'test_value' ) ),
			$this->settings->get_lity_options()
		);

	}

	/**
	 * Test that the options_init() registers the lity_options.
	 */
	public function testOptionsInitRegisterSetting() {

		$this->settings->options_init();

		global $wp_registered_settings;

		$this->assertArrayHasKey( 'lity_options', $wp_registered_settings );

	}

	/**
	 * Test that the options_init() registers the settings sections.
	 */
	public function testOptionsInitRegisterSettingSections() {

		$this->settings->options_init();

		global $wp_settings_sections;

		$this->assertTrue( isset( $wp_settings_sections['lity']['lity_options'] ) );

	}

	/**
	 * Test that the options_init() registers our settings.
	 */
	public function testOptionsInitRegisterSettingFields() {

		$this->settings->options_init();

		global $wp_settings_fields;

		$this->assertEquals(
			[
				'show_full_size'             => true,
				'use_background_image'       => true,
				'show_image_info'            => true,
				'caption_type'               => true,
				'disabled_on'                => true,
				'element_selectors'          => true,
				'excluded_element_selectors' => true,
				'delete_lity_transient'      => true,
				'reset_plugin_settings'      => true,
			],
			[
				'show_full_size'             => isset( $wp_settings_fields['lity']['lity_options']['show_full_size'] ),
				'use_background_image'       => isset( $wp_settings_fields['lity']['lity_options']['use_background_image'] ),
				'show_image_info'            => isset( $wp_settings_fields['lity']['lity_options']['show_image_info'] ),
				'caption_type'               => isset( $wp_settings_fields['lity']['lity_options']['caption_type'] ),
				'disabled_on'                => isset( $wp_settings_fields['lity']['lity_options']['disabled_on'] ),
				'element_selectors'          => isset( $wp_settings_fields['lity']['lity_options']['element_selectors'] ),
				'excluded_element_selectors' => isset( $wp_settings_fields['lity']['lity_options']['excluded_element_selectors'] ),
				'delete_lity_transient'      => isset( $wp_settings_fields['lity']['lity_options']['delete_lity_transient'] ),
				'reset_plugin_settings'      => isset( $wp_settings_fields['lity']['lity_options']['reset_plugin_settings'] ),
			]
		);

	}

	/**
	 * Ensure that the lity_option value 'disabled_on' is never empty when the value is saved, if it is empty.
	 */
	public function testOptionsSanitizeLityOptions() {

		$this->assertEquals(
			[
				'disabled_on' => array(),
			],
			$this->settings->sanitize_lity_options( array() )
		);

		$this->assertEquals(
			[
				'disabled_on' => array( 1, 2 ),
			],
			$this->settings->sanitize_lity_options( array( 'disabled_on' => array( 1, 2 ) ) )
		);

	}

	/**
	 * Ensure that lity_section_developers_callback prints the expected the html output.
	 */
	public function testLitySectionDevelopersCallback() {

		$this->expectOutputRegex( '/<p id="testID">General Settings<\/p>/' );

		$this->settings->lity_section_developers_callback( array( 'id' => 'testID',  ) );

	}

	/**
	 * Ensure that lity_show_full_size_dropdown shows the correct selected value.
	 */
	public function testLityShowFullSizeDropdown() {

		$label_for   = 'show_full_size';
		$description = 'Should full size images be shown in the lightbox?';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the default value is 'yes'.
		$this->expectOutputRegex( '/<option value="yes" selected=\'selected\'>/' );

		$this->settings->lity_show_full_size_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

		$this->settings->update_lity_option( array( $label_for => 'no' ) );

		// Test that 'no' is selected.
		$this->expectOutputRegex( '/<option value="no" selected=\'selected\'>/' );

		$this->settings->lity_show_full_size_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_show_image_info_dropdown prints the expected the html output.
	 */
	public function testLityShowImageInfoDropdown() {

		$label_for   = 'show_image_info';
		$description = 'Should the image title and description be shown in the lightbox?';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the default value is 'yes'.
		$this->expectOutputRegex( '/<option value="yes" selected=\'selected\'>/' );

		$this->settings->lity_show_image_info_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

		$this->settings->update_lity_option( array( $label_for => 'no' ) );

		// Test that 'no' is selected.
		$this->expectOutputRegex( '/<option value="no" selected=\'selected\'>/' );

		$this->settings->lity_show_image_info_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_caption_type_dropdown prints the expected the html output.
	 */
	public function testLityCaptionTypeDropdown() {

		$label_for   = 'caption_type';
		$description = 'Lightbox captions should use which image metadata?';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the default value is 'caption'.
		$this->expectOutputRegex( '/<option value="caption" selected=\'selected\'>/' );

		$this->settings->lity_caption_type_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

		$this->settings->update_lity_option( array( $label_for => 'description' ) );

		// Test that 'description' is selected.
		$this->expectOutputRegex( '/<option value="description" selected=\'selected\'>/' );

		$this->settings->lity_caption_type_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_use_background_image_dropdown prints the expected the html output.
	 */
	public function testLityUseBackgroundImageDropdown() {

		$label_for   = 'use_background_image';
		$description = 'Should the lightbox use the selected image as a background?';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the default value is 'yes'.
		$this->expectOutputRegex( '/<option value="yes" selected=\'selected\'>/' );

		$this->settings->lity_use_background_image_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

		$this->settings->update_lity_option( array( $label_for => 'no' ) );

		// Test that 'no' is selected.
		$this->expectOutputRegex( '/<option value="no" selected=\'selected\'>/' );

		$this->settings->lity_use_background_image_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_show_disabled_on_dropdown prints the expected the html output.
	 */
	public function testLityShowDisabledOnSelect() {

		$post_id_1 = $this->factory->post->create(
			[
				'post_type'    => 'post',
				'post_title'   => 'Sample Post 1',
			]
		);

		$label_for   = 'disabled_on';
		$description = 'Select specific posts or pages that Lity should <strong>not</strong> load on.';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the posts show up in the dropdown.
		$this->expectOutputRegex( "/<option value=\"{$post_id_1}\" >/" );

		$this->settings->lity_show_disabled_on_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

		// Test the select field renders the selected options in the dropdown
		$this->settings->update_lity_option( array( $label_for => array( "$post_id_1" ) ) );

		$this->expectOutputRegex( "/<option value=\"{$post_id_1}\" selected=&quot;selected&quot; >/" );

		$this->settings->lity_show_disabled_on_dropdown( array( 'label_for' => $label_for, 'description' => $description  ) );

	}


	/**
	 * Ensure that lity_show_element_selector_textarea prints the expected the html output.
	 */
	public function testLityShowElementSelectorTextarea() {

		$label_for   = 'element_selectors';
		$description = 'Specify custom element selectors for images on your site. If left empty, this will just be <code>img</code> and target all images on your site.';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the textarea has img in it by default.
		$this->expectOutputRegex( '/<textarea id="' . $label_for . '" name="lity_options\[' . $label_for . '\]" cols="80" rows="10" style="resize: vertical; max-height: 300px;">\[{&quot;value&quot;:&quot;img&quot;}\]<\/textarea>/' );

		$this->settings->lity_show_element_selector_textarea( array( 'label_for' => $label_for, 'description' => $description  ) );

		$this->settings->update_lity_option( array( $label_for => '[{"value":".custom-element"}]' ) );

		$this->expectOutputRegex( '/<textarea id="' . $label_for . '" name="lity_options\[' . $label_for . '\]" cols="80" rows="10" style="resize: vertical; max-height: 300px;">\[{&quot;value&quot;:&quot;.custom-element&quot;}\]<\/textarea>/' );

		$this->settings->lity_show_element_selector_textarea( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_excluded_element_selector_textarea prints the expected the html output.
	 */
	public function testLityShowExcludedElementSelectorTextarea() {

		$label_for   = 'excluded_element_selectors';
		$description = 'Specify element selectors that should be excluded from opening in a lightbox.';

		$this->settings->update_lity_option( $this->settings->default_options );

		// Test the textarea has img in it by default.
		$this->expectOutputRegex( '/<textarea id="' . $label_for . '" name="lity_options\[' . $label_for . '\]" cols="80" rows="10" style="resize: vertical; max-height: 300px;">\[{}\]<\/textarea>/' );

		$this->settings->lity_excluded_element_selector_textarea( array( 'label_for' => $label_for, 'description' => $description  ) );

		$this->settings->update_lity_option( array( $label_for => '[{"value":".excluded-element"}]' ) );

		$this->expectOutputRegex( '/<textarea id="' . $label_for . '" name="lity_options\[' . $label_for . '\]" cols="80" rows="10" style="resize: vertical; max-height: 300px;">\[{&quot;value&quot;:&quot;.excluded-element&quot;}\]<\/textarea>/' );

		$this->settings->lity_excluded_element_selector_textarea( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_clear_transient_button prints the expected the html output.
	 */
	public function testLityClearTransientButtonButtonHTML() {

		$label_for   = 'delete_lity_transient';
		$description = "Clearing the transient data will generate new media data. This can be helpful if data isn&#039;t displaying properly.";

		$this->settings->register_menu_item();

		$this->expectOutputRegex( '/<a href="http:\/\/example.org\/wp-admin\/options-general.php\?page=lity-options&#038;action=lity-regenerate-transient&#038;_wpnonce=/' );

		$this->settings->lity_clear_transient_button( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_reset_plugin_settings_button prints the expected the html output.
	 */
	public function testLityResetPluginSettingsButtonHTML() {

		$label_for   = 'reset_plugin_settings';
		$description = "Reset the plugin settings back to &#039;factory settings&#039;. All options will be reset and cache will be cleared.";

		$this->settings->register_menu_item();

		$this->expectOutputRegex( '/<a href="http:\/\/example.org\/wp-admin\/options-general.php\?page=lity-options&#038;action=lity-reset-plugin-settings&#038;_wpnonce=/' );

		$this->settings->lity_reset_plugin_settings_button( array( 'label_for' => $label_for, 'description' => $description  ) );

	}

	/**
	 * Ensure that lity_options_page prints null when the user is not permitted.
	 */
	public function testLityOptionsPageHTMLNonPermittedUser() {

		wp_set_current_user( self::factory()->user->create( [
			'role' => 'subscriber',
		] ) );

		$this->assertNull( $this->settings->lity_options_page() );

	}

	/**
	 * Ensure that lity_options_page prints the transient cleared notice when the $_GET['lity-transient-cleared'] variable is set.
	 */
	public function testLityOptionsPageTransientClearedNotice() {

		$_GET['lity-transient-cleared'] = true;

		$this->settings->lity_options_page();

		$this->expectOutputRegex( '/<div id="lity-transient-data-cleared-notice" class="notice notice-success">/' );

	}

	/**
	 * Ensure that lity_options_page prints the plugins reset notice when the $_GET['lity-settings-reset'] variable is set.
	 */
	public function testLityOptionsPageSettingsResetNotice() {

		$_GET['lity-settings-reset'] = true;

		$this->settings->lity_options_page();

		$this->expectOutputRegex( '/<div id="lity-settings-reset-notice" class="notice notice-success">/' );

	}

	/**
	 * Ensure that lity_options_page prints the plugins reset notice when the $_GET['lity-settings-reset'] variable is set.
	 */
	public function testLityOptionsPageHTMLCustom() {

		$this->settings->register_menu_item();
		$this->settings->options_init();

		$this->expectOutputRegex( "/<input type='hidden' name='option_page' value='lity' \/>/" );

		$this->settings->lity_options_page();

	}

}
