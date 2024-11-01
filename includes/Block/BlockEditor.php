<?php
namespace WpBean\FormPopup\Block;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class BlockEditor
 *
 * Main BlockEditor class
 *
 * @since 1.0
 */
class BlockEditor {

	/**
	 *  Block_Editor class constructor
	 *
	 * Register Block_Editor action hooks and filters
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'block_editor_assets' ), 10, 0 );
	}

	/**
	 * Register block editor assets
	 */
	public function block_editor_assets() {
		wp_register_script(
			'wpbean-fopo-block-editor',
			plugins_url( 'index.js', __FILE__ ),
			array(
				'wp-api-fetch',
				'wp-components',
				'wp-compose',
				'wp-blocks',
				'wp-element',
				'wp-i18n',
			),
			'1.0',
			false
		);

		wp_set_script_translations(
			'wpbean-fopo-block-editor',
			'wpb-form-popup' // text-domain.
		);

		register_block_type(
			'wpb-form-popup/wpbean-fopo-shortcode-selector',
			array(
				'editor_script' => 'wpbean-fopo-block-editor',
			)
		);

		$shortcode_items = array_map(
			function ( $post ) {
				return array(
					'id'    => $post->ID,
					'title' => '#' . $post->ID . ' ' . get_post_meta( $post->ID, 'wpbean_fopo_shortcode_title', true ),
				);
			},
			get_posts(
				array(
					'post_type'      => 'wpbean_fopo_popups',
					'post_status'    => 'publish',
					'posts_per_page' => '-1',
				)
			)
		);

		wp_add_inline_script(
			'wpbean-fopo-block-editor',
			sprintf(
				'window.wpbfopo = {ShortCodes:%s};',
				wp_json_encode( $shortcode_items )
			),
			'before'
		);
	}
}
