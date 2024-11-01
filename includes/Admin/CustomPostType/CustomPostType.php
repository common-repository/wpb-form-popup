<?php
namespace WpBean\FormPopup\Admin\CustomPostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register ShortCode Post Type Class
 */
class CustomPostType {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_cpt' ) );
	}

	/**
	 * Register CPT for the popups shortcode
	 */
	public function register_cpt() {
		$args = array(
			'label'               => esc_html__( 'WPB Form Popup ShortCodes', 'wpb-form-popup' ),
			'description'         => esc_html__( 'WPB Form Popup ShortCodes', 'wpb-form-popup' ),
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_in_rest'        => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
		);

		register_post_type( 'wpbean_fopo_popups', $args );
	}
}
