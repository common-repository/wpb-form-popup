<?php
namespace WpBean\FormPopup\Admin\AdminPages;

use WpBean\FormPopup\Admin\MetaAPI\MetaAPI;
use function WpBean\FormPopup\enqueue_styles;
use function WpBean\FormPopup\enqueue_scripts;
use function WpBean\FormPopup\form_plugins_meta_fields;
use function WpBean\FormPopup\ninja_forms_meta_fields;
use function WpBean\FormPopup\forminator_forms_meta_fields;
use function WpBean\FormPopup\formidable_forms_meta_fields;
use function WpBean\FormPopup\formmaker_forms_meta_fields;
use function WpBean\FormPopup\fluent_forms_meta_fields;
use function WpBean\FormPopup\ws_forms_meta_fields;
use function WpBean\FormPopup\bitform_meta_fields;
use function WpBean\FormPopup\get_sanitize_callback;
use function WpBean\FormPopup\wpbean_clean;
use function WpBean\FormPopup\sanitize_select2_field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add ShortCodes admin page class
 */
class ShortCodesPage {
	/**
	 * Meta API
	 *
	 * @var [type] void
	 */
	private $meta_api;

	/**
	 * Plugin Version
	 *
	 * @var [type] int
	 */
	public $version = WPBEAN_FOPO_FREE_VERSION;

	/**
	 * Class Constructor.
	 */
	public function __construct() {

		$this->meta_api = new MetaAPI();

		$this->meta_api->set_sections( $this->get_meta_sections() );

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );

		if ( is_admin() && wp_doing_ajax() ) {
			add_action( 'wp_ajax_wpbean_fopo_save_shortcodes_meta', array( $this, 'save_shortcodes_meta' ) );
			add_action( 'wp_ajax_wpbean_fopo_delete_shortcode', array( $this, 'delete_shortcode' ) );
			add_action( 'wp_ajax_wpbean_fopo_add_new_shortcode', array( $this, 'add_new_shortcode' ) );
			add_action( 'wp_ajax_wpbean_fopo_duplicate_shortcode', array( $this, 'duplicate_shortcode' ) );
			add_action( 'wp_ajax_wpbean_fopo_fire_show_shortcode', array( $this, 'show_shortcode' ) );
			add_action( 'wp_ajax_wpbean_fopo_ajax_select2_get_items', array( $this, 'ajax_select2_get_items' ) );
		}
	}

	/**
	 * Returns all the meta sections
	 *
	 * @return array meta sections
	 */
	public function get_meta_sections() {

		$sections = array(
			array(
				'id'    => 'wpbean_fopo_form_settings',
				'title' => esc_html__( 'Form Settings', 'wpb-form-popup' ),
				'desc'  => esc_html__( 'All the form settings are here.', 'wpb-form-popup' ),
				'icon'  => 'data:image/svg+xml;base64,' . base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'<svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22 6H2a1.001 1.001 0 0 0-1 1v3a1.001 1.001 0 0 0 1 1h20a1.001 1.001 0 0 0 1-1V7a1.001 1.001 0 0 0-1-1zm0 4H2V7h20v3h.001M22 17H2a1.001 1.001 0 0 0-1 1v3a1.001 1.001 0 0 0 1 1h20a1.001 1.001 0 0 0 1-1v-3a1.001 1.001 0 0 0-1-1zm0 4H2v-3h20v3h.001M10 14v1H2v-1zM2 3h8v1H2z"/><path fill="none" d="M0 0h24v24H0z"/></svg>'
				), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
			array(
				'id'    => 'wpbean_fopo_btn_settings',
				'title' => esc_html__( 'Button Settings', 'wpb-form-popup' ),
				'desc'  => esc_html__( 'All the button settings are here.', 'wpb-form-popup' ),
				'icon'  => 'data:image/svg+xml;base64,' . base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'<svg width="800px" height="800px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect x="0" fill="none" width="20" height="20"/><g><path d="M17 5H3c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm1 7c0 .6-.4 1-1 1H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h14c.6 0 1 .4 1 1v5z"/></g></svg>'
				), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
			array(
				'id'    => 'wpbean_fopo_btn_style',
				'title' => esc_html__( 'Button or Image Style', 'wpb-form-popup' ),
				'desc'  => esc_html__( 'Modifications to the button\'s or image\'s appearance.', 'wpb-form-popup' ),
				'icon'  => 'data:image/svg+xml;base64,' . base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'<svg fill="#000000" width="800px" height="800px" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg"><path d="m1739.34 1293.414-105.827 180.818-240.225-80.188-24.509 22.25c-69.91 63.586-150.211 109.666-238.644 136.771l-32.076 9.94-49.468 244.065H835.584l-49.468-244.179-32.076-9.939c-88.432-27.105-168.734-73.185-238.644-136.771l-24.508-22.25-240.226 80.189-105.826-180.82 189.74-164.442-7.453-32.978c-10.39-45.742-15.586-91.483-15.586-135.869 0-44.386 5.195-90.127 15.586-135.868l7.454-32.979-189.741-164.442 105.826-180.819 240.226 80.075 24.508-22.25c69.91-63.585 150.212-109.665 238.644-136.884l32.076-9.826 49.468-244.066h213.007l49.468 244.18 32.076 9.825c88.433 27.219 168.734 73.186 238.644 136.885l24.509 22.25 240.225-80.189 105.826 180.819-189.74 164.442 7.453 32.98c10.39 45.74 15.586 91.481 15.586 135.867 0 44.386-5.195 90.127-15.586 135.869l-7.454 32.978 189.741 164.556Zm-53.76-333.403c0-41.788-3.84-84.48-11.634-127.284l210.184-182.062-199.454-340.856-265.186 88.433c-66.974-55.567-143.322-99.388-223.85-128.414L1140.977.01H743.198l-54.663 269.704c-81.431 29.139-156.424 72.282-223.963 128.414L199.5 309.809.045 650.665l210.07 182.062c-7.68 42.804-11.52 85.496-11.52 127.284 0 41.789 3.84 84.48 11.52 127.172L.046 1269.357 199.5 1610.214l265.186-88.546c66.974 55.68 143.323 99.388 223.85 128.527l54.663 269.816h397.779l54.663-269.703c81.318-29.252 156.424-72.283 223.85-128.527l265.186 88.546 199.454-340.857-210.184-182.174c7.793-42.805 11.633-85.496 11.633-127.285ZM942.075 564.706C724.1 564.706 546.782 742.024 546.782 960c0 217.976 177.318 395.294 395.294 395.294 217.977 0 395.294-177.318 395.294-395.294 0-217.976-177.317-395.294-395.294-395.294m0 677.647c-155.633 0-282.353-126.72-282.353-282.353s126.72-282.353 282.353-282.353S1224.43 804.367 1224.43 960s-126.72 282.353-282.353 282.353" fill-rule="evenodd"/></svg>'
				), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
			array(
				'id'    => 'wpbean_fopo_popup_style',
				'title' => esc_html__( 'Popup Settings', 'wpb-form-popup' ),
				'desc'  => esc_html__( 'This is where you set all the popup options and styles.', 'wpb-form-popup' ),
				'icon'  => 'data:image/svg+xml;base64,' . base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'<svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5 8v7h14V8zm13 6H6V9h12zm-7-8H5V5h6zm7-1h1v1h-1zm-6 17.207L15.207 18H20.5a1.502 1.502 0 0 0 1.5-1.5v-13A1.502 1.502 0 0 0 20.5 2h-17A1.502 1.502 0 0 0 2 3.5v13A1.502 1.502 0 0 0 3.5 18h5.293zm9-5.707a.5.5 0 0 1-.5.5h-5.788L12 20.558 9.288 17H3.5a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 .5-.5h17a.5.5 0 0 1 .5.5z"/><path fill="none" d="M0 0h24v24H0z"/></svg>'
				), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
		);

		return apply_filters( 'wpbean_fopo_shortcode_builder_sections', $sections );
	}

	/**
	 * Returns all the meta fields
	 *
	 * @return array meta fields
	 */
	public function get_meta_fields() {

		$fields = array();

		$fields['wpbean_fopo_form_settings'][] = array(
			'name'              => 'wpbean_fopo_shortcode_title',
			'label'             => esc_html__( 'ShortCode Title', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'You can give this shortcode a name for easy reference.', 'wpb-form-popup' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_form_settings'][] = array(
			'name'              => 'wpbean_fopo_form_plugin',
			'label'             => esc_html__( 'Select a Form Plugin', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Select a form plugin that you want to use with our popup.', 'wpb-form-popup' ),
			'type'              => 'select',
			'sanitize_callback' => 'sanitize_text_field',
			'options'           => form_plugins_meta_fields(),
		);

		if ( defined( 'WPCF7_PLUGIN' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_cf7_form',
				'label'             => esc_html__( 'Select a Contact Form 7 Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Contact Form 7 form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'wpcf7_contact_form',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'contact-form-7',
				),
			);
		}

		if ( defined( 'WPFORMS_VERSION' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_wpforms_form',
				'label'             => esc_html__( 'Select a WPForms Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a WPForms form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'wpforms',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'contact-form-by-wpforms',
				),
			);
		}

		if ( defined( 'WPUF_VERSION' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_wpuf_form',
				'label'             => esc_html__( 'Select a WP User Frontend Post Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a WP User Frontend form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'wpuf_forms',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'wp-user-frontend',
				),
			);
		}

		if ( class_exists( 'Ninja_Forms' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_ninja_forms_form',
				'label'             => esc_html__( 'Select a Ninja Forms Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Ninja Forms form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => ninja_forms_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'ninja-forms-contact-form',
				),
			);
		}

		if ( defined( 'FORMINATOR_VERSION' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_forminator_form',
				'label'             => esc_html__( 'Select a Forminator Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Forminator form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => forminator_forms_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'forminator',
				),
			);
		}

		if ( function_exists( 'load_formidable_forms' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_formidable_forms_form',
				'label'             => esc_html__( 'Select a Formidable Forms Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Formidable Forms form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => formidable_forms_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'formidable-forms',
				),
			);
		}

		if ( function_exists( 'wd_form_maker' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_form_maker_form',
				'label'             => esc_html__( 'Select a Form Maker Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Form Maker form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => formmaker_forms_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'form-maker-by-10web',
				),
			);
		}

		if ( class_exists( 'WeForms' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_weforms_form',
				'label'             => esc_html__( 'Select a WeForms Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a WeForms form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'wpuf_contact_form',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'weforms',
				),
			);
		}

		if ( defined( 'FLUENTFORM' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_fluent_form',
				'label'             => esc_html__( 'Select a Fluent Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Fluent Form\'s form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => fluent_forms_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'fluent-forms',
				),
			);

			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_fluent_form_conversational',
				'checkbox_label'    => esc_html__( 'Yes Please!', 'wpb-form-popup' ),
				'label'             => esc_html__( 'Conversational Type Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'If checked, the form shortcode will have a conversational parameter.', 'wpb-form-popup' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'sanitize_text_field',
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'fluent-forms',
				),
			);
		}

		if ( function_exists( 'HappyForms' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_happyforms_form',
				'label'             => esc_html__( 'Select a HappyForms Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a HappyForm\'s form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'happyform',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'happyforms',
				),
			);
		}

		if ( defined( 'WS_FORM_VERSION' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_wsform_form',
				'label'             => esc_html__( 'Select a WS-Form\'s Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a WS-Form\'s form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => ws_forms_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'ws-form',
				),
			);
		}

		if ( class_exists( 'UserRegistration' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_user_registration_form',
				'label'             => esc_html__( 'Select a User Registration Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a user registration form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'user_registration',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'user-registration-by-wpeverest',
				),
			);
		}

		if ( function_exists( '_mc4wp_load_plugin' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_mc4wp_form',
				'label'             => esc_html__( 'Select a mc4wp Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a mc4wp form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'mc4wp-form',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'mc4wp-mailchimp-for-wordpress',
				),
			);
		}

		if ( class_exists( 'EverestForms' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_everest_forms_form',
				'label'             => esc_html__( 'Select a Everest Form\'s Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Everest Form\'s form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'everest_form',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'everest-forms',
				),
			);
		}

		if ( defined( 'KALIFORMS_PLUGIN_FILE' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_kali_forms_form',
				'label'             => esc_html__( 'Select a Kali Form\'s Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Kali Form\'s form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => wp_list_pluck(
					get_posts(
						array(
							'post_type'   => 'kaliforms_forms',
							'numberposts' => -1,
						)
					),
					'post_title',
					'ID'
				),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'kali-forms',
				),
			);
		}

		if ( defined( 'BITFORMS_PLUGIN_MAIN_FILE' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_bitform_form',
				'label'             => esc_html__( 'Select a Bit-Form\'s Form', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Select a Bit-Form\'s form that you created.', 'wpb-form-popup' ),
				'type'              => 'select',
				'sanitize_callback' => 'absint',
				'options'           => bitform_meta_fields(),
				'condition'         => array(
					'wpbean_fopo_form_plugin',
					'bit-form',
				),
			);
		}

		$fields['wpbean_fopo_form_settings'][] = array(
			'name'              => 'wpbean_fopo_without_form_plugin',
			'checkbox_label'    => esc_html__( 'Yes Please!', 'wpb-form-popup' ),
			'label'             => esc_html__( 'Work Without any Form', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'If checked, this popup can be used as a content popup without any form in it.', 'wpb-form-popup' ),
			'type'              => 'checkbox',
			'sanitize_callback' => 'sanitize_text_field',
		);

		if ( ! defined( 'WPBEAN_FOPO_PRO_INIT' ) ) {
			$fields['wpbean_fopo_form_settings'][] = array(
				'name'              => 'wpbean_fopo_popup_custom_content',
				'label'             => esc_html__( 'Popup Content', 'wpb-form-popup' ),
				'desc'              => esc_html__( 'Add any custom content you wish to display in the pop-up here. ShortCode is allowed.', 'wpb-form-popup' ),
				'type'              => 'editor',
				'sanitize_callback' => 'wp_kses_post',
				'options'           => array(
					'media_buttons' => true,
				),
			);
		}

		$fields['wpbean_fopo_btn_settings'][] = array(
			'name'              => 'wpbean_fopo_btn_text',
			'label'             => esc_html__( 'Button Text', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'You can add your own text for the popup button.', 'wpb-form-popup' ),
			'placeholder'       => esc_html__( 'Form Popup', 'wpb-form-popup' ),
			'type'              => 'text',
			'default'           => esc_html__( 'Form Popup', 'wpb-form-popup' ),
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_btn_settings'][] = array(
			'name'              => 'wpbean_fopo_btn_size',
			'label'             => esc_html__( 'Button Size', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Select button size. Default: Medium.', 'wpb-form-popup' ),
			'type'              => 'select',
			'size'              => 'wpbean-fopo-select-buttons',
			'default'           => 'large',
			'sanitize_callback' => 'sanitize_text_field',
			'options'           => array(
				'small'  => esc_html__( 'Small', 'wpb-form-popup' ),
				'medium' => esc_html__( 'Medium', 'wpb-form-popup' ),
				'large'  => esc_html__( 'Large', 'wpb-form-popup' ),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'              => 'wpbean_fopo_btn_align',
			'label'             => esc_html__( 'Button Alignment', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Select button alignment.', 'wpb-form-popup' ),
			'type'              => 'select',
			'size'              => 'wpbean-fopo-select-buttons',
			'default'           => 'default',
			'sanitize_callback' => 'sanitize_text_field',
			'options'           => array(
				'default' => esc_html__( 'Default', 'wpb-form-popup' ),
				'left'    => esc_html__( 'Left', 'wpb-form-popup' ),
				'center'  => esc_html__( 'Center', 'wpb-form-popup' ),
				'right'   => esc_html__( 'Right', 'wpb-form-popup' ),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'       => 'wpbean_fopo_btn_spacing',
			'label'      => esc_html__( 'Button Spacing', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Button padding. Inside spacing.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_btn_spacing_top',
					'label'             => esc_html__( 'Top', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '12', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_spacing_right',
					'label'             => esc_html__( 'Right', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '20', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_spacing_bottom',
					'label'             => esc_html__( 'Bottom', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '12', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_spacing_left',
					'label'             => esc_html__( 'Left', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '20', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'       => 'wpbean_fopo_btn_margin',
			'label'      => esc_html__( 'Button Margin', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Button margin. Outside spacing.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_btn_margin_top',
					'label'             => esc_html__( 'Top', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '20', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_margin_right',
					'label'             => esc_html__( 'Right', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_margin_bottom',
					'label'             => esc_html__( 'Bottom', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '30', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_margin_left',
					'label'             => esc_html__( 'Left', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'              => 'wpbean_fopo_btn_border_radius',
			'label'             => esc_html__( 'Button Border Radius', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Set the button border radius here. Default: 6px', 'wpb-form-popup' ),
			'type'              => 'number',
			'size'              => 'small',
			'default'           => 6,
			'sanitize_callback' => 'absint',
			'placeholder'       => '6',
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'       => 'wpbean_fopo_btn_bg_settings',
			'label'      => esc_html__( 'Button Background', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Button background settings.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_btn_bg',
					'label'             => esc_html__( 'Default', 'wpb-form-popup' ),
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '#F95353',
				),
				array(
					'name'              => 'wpbean_fopo_btn_bg_hover',
					'label'             => esc_html__( 'Hover', 'wpb-form-popup' ),
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '#2B2344',
				),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'       => 'wpbean_fopo_btn_color_settings',
			'label'      => esc_html__( 'Button Text Color', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Button text color settings.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_btn_color',
					'label'             => esc_html__( 'Default', 'wpb-form-popup' ),
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '#ffffff',
				),
				array(
					'name'              => 'wpbean_fopo_btn_color_hover',
					'label'             => esc_html__( 'Hover', 'wpb-form-popup' ),
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '#ffffff',
				),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'              => 'wpbean_fopo_btn_add_border',
			'label'             => esc_html__( 'Add Button Border', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Enable or disable the button border.', 'wpb-form-popup' ),
			'type'              => 'select',
			'size'              => 'wpbean-fopo-select-buttons',
			'default'           => 'no',
			'sanitize_callback' => 'sanitize_text_field',
			'options'           => array(
				'no'  => esc_html__( 'No', 'wpb-form-popup' ),
				'yes' => esc_html__( 'yes', 'wpb-form-popup' ),
			),
		);

		$fields['wpbean_fopo_btn_style'][] = array(
			'name'       => 'wpbean_fopo_btn_border_settings',
			'label'      => esc_html__( 'Button Border', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Button border settings.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_btn_border_width',
					'label'             => esc_html__( 'Width', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_btn_border_type',
					'label'             => esc_html__( 'Type', 'wpb-form-popup' ),
					'type'              => 'select',
					'sanitize_callback' => 'sanitize_text_field',
					'options'           => array(
						''       => esc_html__( 'Default', 'wpb-form-popup' ),
						'none'   => esc_html__( 'None', 'wpb-form-popup' ),
						'solid'  => esc_html__( 'Solid', 'wpb-form-popup' ),
						'double' => esc_html__( 'Double', 'wpb-form-popup' ),
						'dotted' => esc_html__( 'Dotted', 'wpb-form-popup' ),
						'dashed' => esc_html__( 'Dashed', 'wpb-form-popup' ),
						'groove' => esc_html__( 'Groove', 'wpb-form-popup' ),
					),
				),
				array(
					'name'              => 'wpbean_fopo_btn_border_color',
					'label'             => esc_html__( 'Default', 'wpb-form-popup' ),
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field',
				),
				array(
					'name'              => 'wpbean_fopo_btn_border_color_hover',
					'label'             => esc_html__( 'Hover', 'wpb-form-popup' ),
					'type'              => 'color',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
			'condition'  => array(
				'wpbean_fopo_btn_add_border',
				'yes',
			),
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_allow_outside_click',
			'checkbox_label'    => esc_html__( 'Yes Please!', 'wpb-form-popup' ),
			'label'             => esc_html__( 'Close Popup on Background Overlay Click', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'If checked, the user can dismiss the popup by clicking outside it.', 'wpb-form-popup' ),
			'type'              => 'checkbox',
			'default'           => 'on',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_allow_escape_key',
			'checkbox_label'    => esc_html__( 'Yes Please!', 'wpb-form-popup' ),
			'label'             => esc_html__( 'Close Popup on Escape Key', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'If the box is checked, the user can press the Escape key to close the pop-up.', 'wpb-form-popup' ),
			'type'              => 'checkbox',
			'default'           => 'on',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_show_close_icon',
			'checkbox_label'    => esc_html__( 'Yes Please!', 'wpb-form-popup' ),
			'label'             => esc_html__( 'Show Popup Close Icon', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'The pop-up\'s close icon won\'t be visible if the box is unchecked.', 'wpb-form-popup' ),
			'type'              => 'checkbox',
			'default'           => 'on',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_basic_content_style',
			'checkbox_label'    => esc_html__( 'Yes Please!', 'wpb-form-popup' ),
			'label'             => esc_html__( 'Add Basic Style for the Popup Content', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'If enabled, the popup content will have some basic styling, such as typography, margin, etc. If your theme already includes this styling, you can disable it.', 'wpb-form-popup' ),
			'type'              => 'checkbox',
			'default'           => 'on',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'       => 'wpbean_fopo_popup_width',
			'label'      => esc_html__( 'Popup Width', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Popup window width, Can be in px or %. The default width is 650px.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_popup_width_number',
					'label'             => esc_html__( 'Value', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '650', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_popup_width_unit',
					'label'             => esc_html__( 'Unit', 'wpb-form-popup' ),
					'type'              => 'select',
					'size'              => 'wpbean-fopo-select-buttons',
					'default'           => 'px',
					'sanitize_callback' => 'sanitize_text_field',
					'options'           => array(
						'px' => esc_html__( 'Px', 'wpb-form-popup' ),
						'%'  => esc_html__( '%', 'wpb-form-popup' ),
						'em' => esc_html__( 'em', 'wpb-form-popup' ),
					),
				),
			),
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'       => 'wpbean_fopo_popup_height',
			'label'      => esc_html__( 'Popup Height', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Popup window height, Can be in px or %. Remove the value for auto height.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_popup_height_number',
					'label'             => esc_html__( 'Value', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_popup_height_unit',
					'label'             => esc_html__( 'Unit', 'wpb-form-popup' ),
					'type'              => 'select',
					'size'              => 'wpbean-fopo-select-buttons',
					'sanitize_callback' => 'sanitize_text_field',
					'options'           => array(
						'px' => esc_html__( 'Px', 'wpb-form-popup' ),
						'%'  => esc_html__( '%', 'wpb-form-popup' ),
						'em' => esc_html__( 'em', 'wpb-form-popup' ),
					),
				),
			),
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'       => 'wpbean_fopo_popup_overflow',
			'label'      => esc_html__( 'Popup Overflow', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'You can modify popup overflow to suit your needs.', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_popup_overflow_x',
					'label'             => esc_html__( 'Overflow X', 'wpb-form-popup' ),
					'type'              => 'select',
					'default'           => 'hidden',
					'sanitize_callback' => 'sanitize_text_field',
					'options'           => array(
						'visible' => esc_html__( 'visible', 'wpb-form-popup' ),
						'hidden'  => esc_html__( 'hidden', 'wpb-form-popup' ),
						'scroll'  => esc_html__( 'scroll', 'wpb-form-popup' ),
						'auto'    => esc_html__( 'auto', 'wpb-form-popup' ),
					),
				),
				array(
					'name'              => 'wpbean_fopo_popup_overflow_y',
					'label'             => esc_html__( 'Overflow Y', 'wpb-form-popup' ),
					'type'              => 'select',
					'default'           => 'hidden',
					'sanitize_callback' => 'sanitize_text_field',
					'options'           => array(
						'visible' => esc_html__( 'Visible', 'wpb-form-popup' ),
						'hidden'  => esc_html__( 'Hidden', 'wpb-form-popup' ),
						'scroll'  => esc_html__( 'Scroll', 'wpb-form-popup' ),
						'auto'    => esc_html__( 'Auto', 'wpb-form-popup' ),
					),
				),
			),
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_z_index',
			'label'             => esc_html__( 'Popup Z Index', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'If another theme element is overlapping the popup, then increase this value.', 'wpb-form-popup' ),
			'type'              => 'number',
			'size'              => 'small',
			'sanitize_callback' => 'absint',
			'placeholder'       => '9999',
			'default'           => 9999,
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_overlay_popup_bg',
			'label'             => esc_html__( 'Popup Overlay Background', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Popup Overlay background color.', 'wpb-form-popup' ),
			'type'              => 'color',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_bg',
			'label'             => esc_html__( 'Popup Background', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Popup background color. Default: #ffffff', 'wpb-form-popup' ),
			'type'              => 'color',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_color',
			'label'             => esc_html__( 'Popup Color', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Popup text color.', 'wpb-form-popup' ),
			'type'              => 'color',
			'sanitize_callback' => 'sanitize_text_field',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'              => 'wpbean_fopo_popup_border_radius',
			'label'             => esc_html__( 'Popup Border Radius', 'wpb-form-popup' ),
			'desc'              => esc_html__( 'Popup border radius. Default: 0px', 'wpb-form-popup' ),
			'type'              => 'number',
			'size'              => 'small',
			'sanitize_callback' => 'absint',
			'placeholder'       => '10',
		);

		$fields['wpbean_fopo_popup_style'][] = array(
			'name'       => 'wpbean_fopo_popup_spacing',
			'label'      => esc_html__( 'Popup Spacing', 'wpb-form-popup' ),
			'desc'       => esc_html__( 'Popup padding. Inside spacing. Default: 30px 30px 30px 30px', 'wpb-form-popup' ),
			'type'       => 'group',
			'group_type' => 'inline',
			'options'    => array(
				array(
					'name'              => 'wpbean_fopo_popup_spacing_top',
					'label'             => esc_html__( 'Top', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '20', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_popup_spacing_right',
					'label'             => esc_html__( 'Right', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '30', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_popup_spacing_bottom',
					'label'             => esc_html__( 'Bottom', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '20', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
				array(
					'name'              => 'wpbean_fopo_popup_spacing_left',
					'label'             => esc_html__( 'Left', 'wpb-form-popup' ),
					'placeholder'       => esc_html__( '30', 'wpb-form-popup' ),
					'type'              => 'number',
					'size'              => 'small',
					'sanitize_callback' => 'absint',
				),
			),
		);

		return apply_filters( 'wpbean_fopo_shortcode_builder_fields', $fields );
	}

	/**
	 * Save Meta Values
	 */
	public function save_shortcodes_meta() {
		check_ajax_referer( 'wpbean-fopo-add-shortcode-nonce', '_wpbean_fopo_save_shortcodes_meta_nonce' ); // Verify the nonce.

		$forms_data = isset( $_POST['_wpbean_fopo_forms_data'] ) ? array_map( 'WpBean\FormPopup\wpbean_clean', (array) wp_unslash( $_POST['_wpbean_fopo_forms_data'] ) ) : array();

		foreach ( $forms_data as $form_data ) {

			$form_data = json_decode( $form_data );

			$post_id = $form_data->post_id;

			foreach ( $form_data as $meta_key => $meta_value ) {

				$old_value = get_post_meta( absint( $post_id ), $meta_key, true );

				if ( is_array( $meta_value ) ) {
					$array_meta_key = str_replace( array( '[', ']' ), '', $meta_key );
					$array_sanitize = get_sanitize_callback( $array_meta_key, $this->get_meta_fields() );
					$value          = isset( $meta_value ) ? $array_sanitize( wp_unslash( $meta_value ) ) : '';
				} else {
					if ( 'post_id' === $meta_key ) {
						$sanitize = 'absint';
					} else {
						$sanitize = get_sanitize_callback( $meta_key, $this->get_meta_fields() );
					}
					$value = isset( $meta_value ) && '' !== $meta_value ? $sanitize( wp_unslash( $meta_value ) ) : '';
				}

				if ( $old_value !== $value ) {
					update_post_meta( $post_id, $meta_key, $value );
				}
			}
		}

		wp_send_json_success( esc_html__( 'Saved Successfully!', 'wpb-form-popup' ) );
	}

	/**
	 * Delete a ShortCode
	 */
	public function delete_shortcode() {

		check_ajax_referer( 'wpbean-fopo-add-shortcode-nonce', '_wpbean_fopo_delete_shortcode_nonce' ); // Verify the nonce.

		$shortcode_id = isset( $_POST['_wpbean_fopo_shortcode_id'] ) ? intval( wp_unslash( $_POST['_wpbean_fopo_shortcode_id'] ) ) : '';

		if ( $shortcode_id ) {
			wp_delete_post( $shortcode_id, true );

			wp_send_json_success( esc_html__( 'Popup Deleted Successfully.', 'wpb-form-popup' ) );
		}
	}

	/**
	 * Get a ShortCode Item Header HTML.
	 *
	 * @param string $shortcode_id The ShortCode post ID.
	 *
	 * @return void
	 */
	public function get_shortcode_item_header( $shortcode_id ) {
		if ( $shortcode_id ) {
			$title         = get_post_meta( $shortcode_id, 'wpbean_fopo_shortcode_title', true );
			$shortcode_url = wp_nonce_url(
				add_query_arg( 'popup_id', $shortcode_id, admin_url( '/admin.php?page=' . WPBEAN_FOPO_ADMIN_PAGE ) ),
				'wpbean_fopo_shortcode_item_page',
				'wpbean_fopo_shortcode_item_page_nonce'
			);
			?>
			<div class="wpbean-fopo-shortcodes-list-item-wrapper" data-id="<?php echo esc_attr( $shortcode_id ); ?>">
				<div class="wpbean-fopo-shortcode-header">
					<div class="wpbean-fopo-shortcode-header-inner">
						<div class="wpbean-fopo-shortcode-header-left">
							<h3>
								<a href="<?php echo esc_url( $shortcode_url ); ?>">
									<?php echo ( $title ? '#' . esc_attr( $shortcode_id ) . ' ' . esc_html( $title ) : '#' . esc_attr( $shortcode_id ) ); ?>
								</a>
							</h3>
						</div>
						<div class="wpbean-fopo-shortcode-header-right">
						<div class="wpbean-fopo-shortcodes-actions">
							<span class="wpbean-fopo-shortcode-delete" title="<?php esc_html_e( 'Delete', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></span>
							<span class="wpbean-fopo-shortcode-duplicate" title="<?php esc_html_e( 'Duplicate', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></span>
							<span class="wpbean-fopo-shortcode-shortcode-popup" title="<?php esc_html_e( 'ShortCode', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg></span>
							<span class="wpbean-fopo-shortcode-expand" title="<?php esc_html_e( 'Edit ShortCode', 'wpb-form-popup' ); ?>"><a href="<?php echo esc_url( $shortcode_url ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a></span>
						</div>
						</div>
					</div>
				</div>
				<span class="wpb-fopo-c-ripple wpb-fopo-js-ripple"><span class="wpb-fopo-c-ripple__circle"></span></span>
			</div>
			<?php
		}
	}

	/**
	 * Add new popup ShortCode
	 */
	public function add_new_shortcode() {

		check_ajax_referer( 'wpbean-fopo-add-shortcode-nonce', '_wpbean_fopo_add_shortcode_nonce' ); // Verify the nonce.

		$post = array(
			'post_status' => 'publish',
			'post_type'   => 'wpbean_fopo_popups',
		);

		$shortcode_id = wp_insert_post( $post );

		ob_start();
		$this->get_shortcode_item_header( $shortcode_id );
		$shortcode_html = ob_get_clean();

		return wp_send_json_success(
			array(
				'id'      => $shortcode_id,
				'content' => $shortcode_html,
			)
		);
	}

	/**
	 * Duplicate a ShortCode
	 */
	public function duplicate_shortcode() {

		check_ajax_referer( 'wpbean-fopo-add-shortcode-nonce', '_wpbean_fopo_duplicate_shortcode_nonce' ); // Verify the nonce.

		$shortcode_id = isset( $_POST['_wpbean_fopo_shortcode_id'] ) ? intval( wp_unslash( $_POST['_wpbean_fopo_shortcode_id'] ) ) : '';

		$oldpost = get_post( $shortcode_id );
		$data    = get_post_custom( $shortcode_id );
		$post    = array(
			'post_status' => 'publish',
			'post_type'   => 'wpbean_fopo_popups',
		);

		$new_post_id = wp_insert_post( $post );

		// Copy post metadata.
		foreach ( $data as $key => $values ) {
			foreach ( $values as $value ) {
				if ( 'wpbean_fopo_shortcode_title' === $key ) {
					$value = $value . esc_html__( ' Copy', 'wpb-form-popup' );
				}
				add_post_meta( $new_post_id, $key, $value );
			}
		}

		ob_start();
		$this->get_shortcode_item_header( $new_post_id );
		$shortcode_html = ob_get_clean();

		return wp_send_json_success( $shortcode_html );
	}

	/**
	 * Show a ShortCode
	 *
	 * @return void
	 */
	public function show_shortcode() {

		check_ajax_referer( 'wpbean-fopo-add-shortcode-nonce', '_wpbean_fopo_show_shortcode_nonce' ); // Verify the nonce.

		$shortcode_id  = isset( $_POST['_wpbean_fopo_shortcode_id'] ) ? intval( wp_unslash( $_POST['_wpbean_fopo_shortcode_id'] ) ) : '';
		$tabs          = array( 'ShortCode', 'PHP Code', 'Widget', 'Gutenberg', 'Elementor' );
		$shortcode_tag = 'wpbean-fopo-form-popup';

		if ( $shortcode_id ) {
			ob_start();
			?>
				<div class="wpbean-fopo-shortcode-popup-wrapper">
					<?php if ( isset( $tabs ) && ! empty( $tabs ) ) : ?>
						<div class="wpbean-fopo-shortcode-tabs">
							<div class="wpbean-fopo-shortcode-tabs-nav">
								<ul>
									<?php
									foreach ( $tabs as $tab ) {
										printf( '<li><a href="#wpbean-fopo-tab-%s">%s</a></li>', esc_attr( sanitize_title( $tab ) ), esc_html( $tab ) );
									}
									?>
								</ul>
							</div>

							<div class="wpbean-fopo-shortcode-tabs-content-wrapper">
								<?php foreach ( $tabs as $tab ) : ?>
									<div id="wpbean-fopo-tab-<?php echo esc_attr( sanitize_title( $tab ) ); ?>" class="wpbean-fopo-shortcode-tab-content">
										<?php
										switch ( sanitize_title( $tab ) ) {
											case 'shortcode':
												printf( '<p>%s</p>', esc_html__( 'Copy the code below, and then put it wherever you want it to show up.', 'wpb-form-popup' ) );
												printf( '<div class="wpbean-fopo-copy-shortcode-wrapper"><div class="wpbean-fopo-copy-shortcode"><div class="wpbean-fopo-copy-shortcode-text">[%s id="%s"]</div><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></div></div>', esc_attr( $shortcode_tag ), esc_attr( $shortcode_id ) );
												break;

											case 'php-code':
												printf( '<p>%s</p>', esc_html__( 'This PHP code can be used by advanced users to show the popup.                                                    ', 'wpb-form-popup' ) );
												printf( '<div class="wpbean-fopo-copy-shortcode-wrapper"><div class="wpbean-fopo-copy-shortcode"><div class="wpbean-fopo-copy-shortcode-text"> &#60;?php echo do_shortcode(\'[%s id="%s"]\'); ?&#62; </div><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></div></div>', esc_attr( $shortcode_tag ), esc_attr( $shortcode_id ) );
												break;

											case 'widget':
												printf( '<p>%s</p>', esc_html__( 'Navigate to the widgets page and drag and drop the plugin\'s widget into the desired spot.', 'wpb-form-popup' ) );
												printf( '<a href="%s" class="button button-secondary button-block">%s</a>', esc_url( admin_url( 'widgets.php' ) ), esc_html__( 'Go to the Widgets Page', 'wpb-form-popup' ) );
												break;

											case 'elementor':
												printf( '<p>%s</p>', esc_html__( 'Navigate to the Elementor editor page and drag and drop the plugin\'s widgets into the desired spot.', 'wpb-form-popup' ) );
												break;

											case 'gutenberg':
												printf( '<p>%s</p>', esc_html__( 'Navigate to the Gutenberg editor page and drag and drop the plugin\'s block into the desired spot.', 'wpb-form-popup' ) );
												break;

											default:
												break;
										}
										?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			<?php
			$content = ob_get_clean();
			wp_send_json_success(
				array(
					'title'   => esc_html__( 'Form Popup #', 'wpb-form-popup' ) . esc_html( $shortcode_id ),
					'content' => $content,
				)
			);
		}
	}

	/**
	 * Add to Admin menu
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_menu_page(
			esc_html__( 'WPB Form Popup', 'wpb-form-popup' ),
			esc_html__( 'WPB Form Popup', 'wpb-form-popup' ),
			'manage_options',
			WPBEAN_FOPO_ADMIN_PAGE,
			array( $this, 'popup_shortcodes_admin_page' ),
			'data:image/svg+xml;base64,' . base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 122.88 120.45" style="enable-background:new 0 0 122.88 120.45" xml:space="preserve"><style type="text/css"><![CDATA[
                .st0{fill-rule:evenodd;clip-rule:evenodd;}
            ]]></style><g><path class="st0" d="M8.89,0h105.1c4.9,0,8.89,4,8.89,8.89v102.68c0,4.88-4,8.89-8.89,8.89H8.89c-4.88,0-8.89-4-8.89-8.89V8.89 C-0.01,4,3.99,0,8.89,0L8.89,0L8.89,0z M66.17,91.29h30.49c1.95,0,3.54,1.61,3.54,3.54v6.69c0,1.94-1.61,3.54-3.54,3.54H66.17 c-1.93,0-3.54-1.59-3.54-3.54v-6.69C62.63,92.88,64.22,91.29,66.17,91.29L66.17,91.29z M26.23,60.79h70.42 c1.71,0,3.27,0.7,4.41,1.84l0.01,0.01c1.13,1.13,1.83,2.69,1.83,4.41v8.85c0,1.71-0.7,3.28-1.83,4.41l-0.01,0.01 c-1.13,1.13-2.69,1.83-4.41,1.83H26.23c-1.71,0-3.27-0.7-4.41-1.83l-0.01-0.01c-1.13-1.13-1.83-2.69-1.83-4.41v-8.85 c0-1.71,0.7-3.27,1.83-4.41l0.01-0.01C22.96,61.49,24.52,60.79,26.23,60.79L26.23,60.79z M96.65,66.21H26.23 c-0.23,0-0.44,0.09-0.59,0.24c-0.15,0.15-0.24,0.36-0.24,0.59v8.85c0,0.23,0.09,0.44,0.24,0.59c0.15,0.15,0.36,0.24,0.59,0.24 h70.42c0.23,0,0.44-0.09,0.59-0.24c0.15-0.15,0.24-0.36,0.24-0.59v-8.85c0-0.23-0.09-0.44-0.24-0.59 C97.09,66.3,96.88,66.21,96.65,66.21L96.65,66.21z M26.23,33h70.42c1.71,0,3.27,0.7,4.41,1.83l0.01,0.01 c1.13,1.13,1.83,2.69,1.83,4.41v8.85c0,1.71-0.7,3.28-1.83,4.41l-0.01,0.01c-1.13,1.13-2.69,1.83-4.41,1.83H26.23 c-1.71,0-3.27-0.7-4.41-1.83l-0.01-0.01c-1.13-1.13-1.83-2.69-1.83-4.41v-8.85c0-1.71,0.7-3.27,1.83-4.41l0.01-0.01 C22.96,33.7,24.52,33,26.23,33L26.23,33z M96.65,38.42H26.23c-0.23,0-0.44,0.09-0.59,0.24c-0.15,0.15-0.24,0.36-0.24,0.59v8.85 c0,0.23,0.09,0.44,0.24,0.59c0.15,0.15,0.36,0.24,0.59,0.24h70.42c0.23,0,0.44-0.09,0.59-0.24c0.15-0.15,0.24-0.36,0.24-0.59v-8.85 c0-0.23-0.09-0.44-0.24-0.59C97.09,38.51,96.88,38.42,96.65,38.42L96.65,38.42z M114.8,27.01H8.46v82.6c0,0.68,0.27,1.28,0.72,1.74 c0.46,0.46,1.06,0.72,1.74,0.72H112.3c0.68,0,1.28-0.27,1.74-0.72c0.46-0.46,0.72-1.06,0.72-1.74v-82.6H114.8L114.8,27.01 L114.8,27.01z M105.53,9.64c2.42,0,4.39,1.97,4.39,4.39c0,2.42-1.96,4.39-4.39,4.39c-2.43,0-4.39-1.97-4.39-4.39 C101.13,11.6,103.09,9.64,105.53,9.64L105.53,9.64L105.53,9.64z M75.76,9.64c2.42,0,4.39,1.97,4.39,4.39 c0,2.42-1.97,4.39-4.39,4.39s-4.39-1.97-4.39-4.39C71.36,11.6,73.32,9.64,75.76,9.64L75.76,9.64L75.76,9.64z M90.64,9.64 c2.42,0,4.39,1.97,4.39,4.39c0,2.42-1.97,4.39-4.39,4.39s-4.39-1.97-4.39-4.39C86.25,11.6,88.21,9.64,90.64,9.64L90.64,9.64 L90.64,9.64z"/></g></svg>'
			), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			apply_filters( 'wpbean_fopo_admin_page_position', 30 ),
		);

		add_submenu_page(
			WPBEAN_FOPO_ADMIN_PAGE,
			esc_html__( 'WPB Popup Builder', 'wpb-form-popup' ),
			esc_html__( 'Popup Builder', 'wpb-form-popup' ),
			'manage_options',
			WPBEAN_FOPO_ADMIN_PAGE,
			array( $this, 'popup_shortcodes_admin_page' ),
		);
	}

	/**
	 * Get a ShortCode Item Header + Body HTML
	 *
	 * @param int $shortcode_id The ShortCode post ID here.
	 * @return void
	 */
	public function get_shortcode_item( $shortcode_id ) {
		$title = get_post_meta( $shortcode_id, 'wpbean_fopo_shortcode_title', true );
		if ( $shortcode_id ) {
			?>
			<div class="wpbean-fopo-shortcodes-list-item-wrapper" data-id="<?php echo esc_attr( $shortcode_id ); ?>">
				<div class="wpbean-fopo-shortcode-header">
					<div class="wpbean-fopo-shortcode-header-inner">
						<div class="wpbean-fopo-shortcode-header-left">
							<span class="wpbean-fopo-back-to-shortcodes-page" title="<?php esc_html_e( 'Back', 'wpb-form-popup' ); ?>"><a href="<?php echo esc_url( remove_query_arg( array( 'popup_id' ), admin_url( '/admin.php?page=' . WPBEAN_FOPO_ADMIN_PAGE ) ) ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a></span>
							<h3><?php echo ( $title ? ' ' . esc_html( $title ) : '#' . esc_attr( $shortcode_id ) ); ?></h3>
						</div>
						<div class="wpbean-fopo-shortcode-header-right">
						<div class="wpbean-fopo-shortcodes-actions">
							<span class="wpbean-fopo-shortcode-shortcode-popup" title="<?php esc_html_e( 'ShortCode', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg></span>
						</div>
						</div>
					</div>
				</div>
				<form class="wpbean-fopo-shortcodes-list-item wpbean-fopo-shortcodes-list-item-<?php echo esc_attr( $shortcode_id ); ?>" data-id="<?php echo esc_attr( $shortcode_id ); ?>">
					<?php
						$this->meta_api->show_navigation( $shortcode_id );
						$this->meta_api->show_fields( $this->get_meta_fields(), $shortcode_id );
					?>
				</form>
			</div>
			<?php
		}
	}

	/**
	 * Get a ShortCode Item Header HTML
	 *
	 * @param int $shortcode_id The ShortCode post ID here.
	 * @return void
	 */
	public function get_shortcode_item_header__( $shortcode_id ) {
		if ( $shortcode_id ) {
			$title         = get_post_meta( $shortcode_id, 'wpbean_fopo_shortcode_title', true );
			$shortcode_url = wp_nonce_url(
				add_query_arg( 'popup_id', $shortcode_id, admin_url( '/admin.php?page=' . WPBEAN_FOPO_ADMIN_PAGE ) ),
				'wpbean_fopo_shortcode_item_page',
				'wpbean_fopo_shortcode_item_page_nonce'
			);

			?>
			<div class="wpbean-fopo-shortcodes-list-item-wrapper" data-id="<?php echo esc_attr( $shortcode_id ); ?>">
				<div class="wpbean-fopo-shortcode-header">
					<div class="wpbean-fopo-shortcode-header-inner">
						<div class="wpbean-fopo-shortcode-header-left">
							<h3>
								<a href="<?php echo esc_url( $shortcode_url ); ?>">
									<?php echo ( $title ? '#' . esc_attr( $shortcode_id ) . ' ' . esc_html( $title ) : '#' . esc_attr( $shortcode_id ) ); ?>
								</a>
							</h3>
						</div>
						<div class="wpbean-fopo-shortcode-header-right">
						<div class="wpbean-fopo-shortcodes-actions">
							<?php if ( defined( 'WPBEAN_FOPO_PRO_INIT' ) ) : ?>
								<span class="wpbean-fopo-shortcode-delete" title="<?php esc_html_e( 'Delete', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></span>
								<span class="wpbean-fopo-shortcode-duplicate" title="<?php esc_html_e( 'Duplicate', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></span>
							<?php endif; ?>
							<span class="wpbean-fopo-shortcode-shortcode-popup" title="<?php esc_html_e( 'ShortCode', 'wpb-form-popup' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg></span>
							<span class="wpbean-fopo-shortcode-expand" title="<?php esc_html_e( 'Edit ShortCode', 'wpb-form-popup' ); ?>"><a href="<?php echo esc_url( $shortcode_url ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a></span>
						</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Select2 get items ajax
	 *
	 * @return void
	 */
	public function ajax_select2_get_items() {

		check_ajax_referer( 'wpbean-fopo-add-shortcode-nonce', '_wpbean_fopo_select2_nonce' ); // Verify the nonce.

		$output    = array();
		$data_type = ( ! empty( $_GET['data_type'] ) ? sanitize_text_field( wp_unslash( $_GET['data_type'] ) ) : '' );
		$post_type = ( ! empty( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '' );
		$taxonomy  = ( ! empty( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '' );

		$public_post_types = get_post_types(
			array(
				'public'  => true,
				'show_ui' => true,
			)
		);
		$post_types_not_in = array( 'attachment', 'fmemailverification', 'form-maker' );

		$public_taxonomies = get_taxonomies( array( 'public' => true ) );
		$taxonomies_not_in = array( 'post_format', 'product_shipping_class' );

		if ( 'post' === $data_type && $post_type ) {
			$posts = get_posts(
				array(
					's'                   => ( ! empty( $_GET['wpbean_fopo_ajax_select2_search_query'] ) ) ? sanitize_text_field( wp_unslash( $_GET['wpbean_fopo_ajax_select2_search_query'] ) ) : '',
					'post_status'         => 'publish',
					'ignore_sticky_posts' => 1,
					'posts_per_page'      => 100,
					'post_type'           => ( 'any_public' === $post_type ? array_diff( $public_post_types, $post_types_not_in ) : $post_type ),
				)
			);

			if ( $posts && ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					if ( 'any_public' === $post_type ) {
						$output[] = array( $post->ID, '#' . esc_attr( $post->ID ) . ' ' . esc_html( $post->post_title ) . ' (' . get_post_type( $post->ID ) . ')' );
					} else {
						$output[] = array( $post->ID, esc_html( $post->post_title ) );
					}
				}
			}
		} elseif ( 'taxonomy' === $data_type && 'null' !== $taxonomy ) {
			$terms = get_terms(
				array(
					'search'     => ( ! empty( $_GET['wpbean_fopo_ajax_select2_search_query'] ) ) ? sanitize_text_field( wp_unslash( $_GET['wpbean_fopo_ajax_select2_search_query'] ) ) : '',
					'taxonomy'   => ( 'any_public' === $taxonomy ? array_diff( $public_taxonomies, $taxonomies_not_in ) : $taxonomy ),
					'hide_empty' => false,
					'number'     => 100,
				)
			);

			if ( $terms && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {

					if ( 'any_public' === $taxonomy ) {
						$output[] = array( $term->term_id, '#' . esc_attr( $term->term_id ) . ' ' . esc_html( $term->name ) . ' (' . get_term( $term->term_id )->taxonomy . ')' );
					} else {
						$output[] = array( $term->term_id, $term->name );
					}
				}
			}
		}

		wp_send_json_success( $output );
	}

	/**
	 * The Admin Page for ShortCodes
	 *
	 * @return void
	 */
	public function popup_shortcodes_admin_page() {
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
		enqueue_styles( array( 'wpb-form-popup-select2', 'wp-color-picker', 'wpb-form-popup-jbox', 'wpb-form-popup-add-shortcode-style' ) );
		enqueue_scripts( array( 'wpb-form-popup-select2', 'wp-color-picker-alpha', 'wpb-form-popup-select-togglebutton', 'wpb-form-popup-jbox', 'wpb-form-popup-admin' ) );

		$requested_popup_id = '';

		if ( ! empty( $_GET['popup_id'] ) ) { // WPCS: input var ok.
			check_admin_referer( 'wpbean_fopo_shortcode_item_page', 'wpbean_fopo_shortcode_item_page_nonce' );

			$requested_popup_id = absint( wp_unslash( $_GET['popup_id'] ) );
		}
		?>
		<div class="wpbean-fopo-shortcode-list-page-content<?php echo esc_attr( defined( 'WPBEAN_FOPO_PRO_INIT' ) ? ' wpbean-fopo-pro-shortcode-list-page-content' : ' wpbean-fopo-free-shortcode-list-page-content' ); ?>" style="display:none">
			<div class="wpbean-fopo-list-header">
				<div class="wpbean-fopo-logo-and-page-title"><h3><?php esc_html_e( 'WPB Form Popup', 'wpb-form-popup' ); ?></h3></div>
				<div class="wpbean-fopo-pro-and-version">
					<?php
						printf( '<span class="wpbean-fopo-plan-status"><span class="wpbean-fopo-label">%s</span><span class="wpbean-fopo-text">%s</span></span>', esc_html__( 'You are on the', 'wpb-form-popup' ), ( defined( 'WPBEAN_FOPO_PRO_INIT' ) ? esc_html__( 'Pro Version', 'wpb-form-popup' ) : esc_html__( 'Free Version', 'wpb-form-popup' ) ) );
						printf( '<span class="wpbean-fopo-version"><span class="wpbean-fopo-label">%s</span><span class="wpbean-fopo-text">%s</span></span>', esc_html__( 'Version', 'wpb-form-popup' ), ( defined( 'WPBEAN_FOPO_PRO_VERSION' ) ? esc_html( WPBEAN_FOPO_PRO_VERSION ) : esc_html( $this->version ) ) );
					if ( ! defined( 'WPBEAN_FOPO_PRO_INIT' ) ) {
						echo ( $this->version ? sprintf( '<span class="wpbean-fopo-version"><span class="wpbean-fopo-label">%s</span><span class="wpbean-fopo-text">%s</span></span>', esc_html__( 'Upgrade to Pro and Save 10%', 'wpb-form-popup' ), esc_html( '10PERCENTOFF' ) ) : '' );
						echo ( WPBEAN_FOPO_PREMIUM_VERSION_URL ? sprintf( '<a class="button" href="%s" target="_blank">%s</a>', esc_url( WPBEAN_FOPO_PREMIUM_VERSION_URL ) . '?utm_content=WPB+Form+Popup+Pro&utm_campaign=adminlink&utm_medium=admin-page&utm_source=FreeVersion', esc_html__( 'Go Pro', 'wpb-form-popup' ) ) : '' );
					}
					?>
				</div>
			</div>
			<div class="wpbean-fopo-list-content">
				<div class="wpbean-fopo-list-content-left">
					<?php do_action( 'wpbean_fopo_before_shortcode_list' ); ?>
					<div class="wpbean-fopo-list-wrapper">
						<div class="wpbean-fopo-section-header wpbean-fopo-list-items-header<?php echo ( '' !== $requested_popup_id ? ' wpbean-fopo-section-header-shortcode-edit-page' : '' ); ?>">
							<?php if ( '' === $requested_popup_id ) : ?>
								<?php printf( '<h3><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M15.8,13.7H61c1,0,1.8-0.8,1.8-1.8S62,10.2,61,10.2H15.8c-1,0-1.8,0.8-1.8,1.8S14.9,13.7,15.8,13.7z"/><path d="M61,30.3H15.8c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H61c1,0,1.8-0.8,1.8-1.8S62,30.3,61,30.3z"/><path d="M61,50.3H15.8c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H61c1,0,1.8-0.8,1.8-1.8S62,50.3,61,50.3z"/><path d="M5.8,9.1C4.2,9.1,3,10.4,3,11.9s1.2,2.8,2.8,2.8s2.8-1.2,2.8-2.8S7.3,9.1,5.8,9.1z"/><path d="M5.8,29.2C4.2,29.2,3,30.5,3,32c0,1.5,1.2,2.8,2.8,2.8s2.8-1.2,2.8-2.8C8.6,30.5,7.3,29.2,5.8,29.2z"/><path d="M5.8,49.3c-1.5,0-2.8,1.2-2.8,2.8s1.2,2.8,2.8,2.8s2.8-1.2,2.8-2.8S7.3,49.3,5.8,49.3z"/></g></svg> %s</h3>', esc_html__( 'List of Form Popups', 'wpb-form-popup' ) ); ?>
								<a class="<?php echo esc_attr( apply_filters( 'wpbean_fopo_add_new_popup_btn_classes', 'button button-primary wpbean-fopo-button wpbean-fopo-add-new-popup' ) ); ?>"><?php echo esc_html__( 'Add New Popup ', 'wpb-form-popup' ); ?> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M61,30.3H33.8V3c0-1-0.8-1.8-1.8-1.8S30.3,2,30.3,3v27.3H3c-1,0-1.8,0.8-1.8,1.8S2,33.8,3,33.8h27.3V61c0,1,0.8,1.8,1.8,1.8s1.8-0.8,1.8-1.8V33.8H61c1,0,1.8-0.8,1.8-1.8S62,30.3,61,30.3z"/></svg></a>
							<?php else : ?>
								<?php
									$shortcode_title = get_post_meta( $requested_popup_id, 'wpbean_fopo_shortcode_title', true );
								?>
								<?php printf( '<h3><svg viewBox="0 -3.5 21 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000"><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Dribbble-Light-Preview" transform="translate(-99.000000, -760.000000)" fill="#ffffff"><g id="icons" transform="translate(56.000000, 160.000000)"><path d="M53.5,603 C53.5,602.647 53.5756,602 53.6932,602 L43,602 L43,604 L53.6932,604 C53.5756,604 53.5,603.353 53.5,603 L53.5,603 Z M61.7068,602 C61.27315,601 60.1192,600 58.75,600 C57.01015,600 55.6,601.343 55.6,603 C55.6,604.657 57.01015,606 58.75,606 C60.1192,606 61.27315,605 61.7068,604 L64,604 L64,602 L61.7068,602 Z M53.5,611 C53.5,611.353 53.4244,611.686 53.3068,612 L64,612 L64,610 L53.3068,610 C53.4244,610 53.5,610.647 53.5,611 L53.5,611 Z M51.4,611 C51.4,612.657 49.98985,614 48.25,614 C46.8808,614 45.72685,613 45.2932,612 L43,612 L43,610 L45.2932,610 C45.72685,609 46.8808,608 48.25,608 C49.98985,608 51.4,609.343 51.4,611 L51.4,611 Z" id="settings-[#ffffff]"></path></g></g></g></svg> <span>%s %s<span class="shortcode-title"> - %s</span></span></h3>', esc_html__( 'Edit Popup #', 'wpb-form-popup' ), esc_html( $requested_popup_id ), esc_html( $shortcode_title ) ); ?>
								<button class="button button-primary wpbean-fopo-button wpb-button-large wpbean-fopo-save-meta-button"><?php esc_html_e( 'Save Changes', 'wpb-form-popup' ); ?></button>
							<?php endif; ?>
						</div>
						<div class="wpbean-fopo-shortcodes-list-items">
							<?php
								$shortcode_items = get_posts(
									array(
										'post_type'   => 'wpbean_fopo_popups',
										'post_status' => 'publish',
										'ignore_sticky_posts' => 1,
										'numberposts' => -1,
										'fields'      => 'ids',
									)
								);

							if ( $requested_popup_id && '' !== $requested_popup_id ) {
								$this->get_shortcode_item( $requested_popup_id );
							} elseif ( $shortcode_items && ! empty( $shortcode_items ) ) {
								foreach ( $shortcode_items as $shortcode_item ) {
									$this->get_shortcode_item_header( $shortcode_item );
								}
							} else {
								printf( '<div class="wpbean-fopo-alert wpbean-fopo-alert-error">%s</div>', esc_html__( 'No Popup ShortCode Found. Please Add Some.', 'wpb-form-popup' ) );
							}
							?>
						</div>
						<?php if ( $requested_popup_id && '' !== $requested_popup_id ) : ?>
							<div class="wpbean-fopo-form-button-wrapper">
								<button class="button button-primary wpbean-fopo-button wpb-button-large wpbean-fopo-save-meta-button"><?php esc_html_e( 'Save Changes', 'wpb-form-popup' ); ?></button>
							</div>
						<?php endif; ?>
					</div>
					<?php do_action( 'wpbean_fopo_after_shortcode_list' ); ?>
				</div>
				<div class="wpbean-fopo-list-content-right">
					<div class="wpbean-fopo-list-additional-informations">
					<div class="wpbean-fopo-section-header wpbean-fopo-info-header">
						<h3><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><g><path d="M15.8,13.7H61c1,0,1.8-0.8,1.8-1.8S62,10.2,61,10.2H15.8c-1,0-1.8,0.8-1.8,1.8S14.9,13.7,15.8,13.7z"></path><path d="M61,30.3H15.8c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H61c1,0,1.8-0.8,1.8-1.8S62,30.3,61,30.3z"></path><path d="M61,50.3H15.8c-1,0-1.8,0.8-1.8,1.8s0.8,1.8,1.8,1.8H61c1,0,1.8-0.8,1.8-1.8S62,50.3,61,50.3z"></path><path d="M5.8,9.1C4.2,9.1,3,10.4,3,11.9s1.2,2.8,2.8,2.8s2.8-1.2,2.8-2.8S7.3,9.1,5.8,9.1z"></path><path d="M5.8,29.2C4.2,29.2,3,30.5,3,32c0,1.5,1.2,2.8,2.8,2.8s2.8-1.2,2.8-2.8C8.6,30.5,7.3,29.2,5.8,29.2z"></path><path d="M5.8,49.3c-1.5,0-2.8,1.2-2.8,2.8s1.2,2.8,2.8,2.8s2.8-1.2,2.8-2.8S7.3,49.3,5.8,49.3z"></path></g></svg> Help</h3>
					</div>
					<div class="wpbean-fopo-info-items">
						<div class="wpbean-fopo-info-doc">
							<h3><?php esc_html_e( 'Documentation', 'wpb-form-popup' ); ?></h3>
							<p><?php esc_html_e( 'Do you still have questions about how to use this plugin? Please see our detailed documentation here.', 'wpb-form-popup' ); ?></p>
							<a href="https://docs.wpbean.com/?p=1836" target="_blank" class="button button-secondary"><?php esc_html_e( 'To Documentation', 'wpb-form-popup' ); ?></a>
						</div>
						<div class="wpbean-fopo-info-support">
							<h3><?php esc_html_e( 'Ask a Question', 'wpb-form-popup' ); ?></h3>
							<p><?php esc_html_e( 'Do you have any problems with this plugin? Please do not hesitate to file a support request.', 'wpb-form-popup' ); ?></p>
							<a href="https://wpbean.com/support/" target="_blank" class="button button-secondary"><?php esc_html_e( 'To Support', 'wpb-form-popup' ); ?></a>
						</div>
						<?php if ( ! defined( 'WPBEAN_FOPO_PRO_INIT' ) ) : ?>
							<div class="wpbean-fopo-info-support">
								<h3><?php esc_html_e( 'Upgrade 10% OFF Sale', 'wpb-form-popup' ); ?></h3>
								<p><?php printf( '%s %s <b>%s</b>', esc_html__( 'Get a 10% exclusive discount on the premium version.', 'wpb-form-popup' ), esc_html__( 'Use discount code - ', 'wpb-form-popup' ), '10PERCENTOFF' ); ?></p>
								<?php printf( '<a href="%s" target="_blank" class="button button-secondary">%s</a>', esc_url( WPBEAN_FOPO_PREMIUM_VERSION_URL . '?utm_content=WPB+Form+Popup+Pro&utm_campaign=adminlink&utm_medium=admin-page&utm_source=FreeVersion' ), esc_html__( 'Go Pro', 'wpb-form-popup' ) ); ?>
							</div>
						<?php endif; ?>
					</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}