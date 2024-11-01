<?php
namespace WpBean\FormPopup\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Form Popup Widget.
 *
 * @since 1.0.0
 */
class ElementorWidget extends \Elementor\Widget_Base {

	/**
	 * Retrieve the posts of a custom post types.
	 *
	 * @param string $post_type The custom post type name.
	 * @return array
	 */
	private function get_all_posts( $post_type = 'any' ) {
		$posts = get_posts(
			array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		$output = array();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$output[ $post->ID ] = '#' . esc_attr( $post->ID ) . ' ' . get_post_meta( $post->ID, 'wpbean_fopo_shortcode_title', true );
			}
		}

		return $output;
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve Form Popup widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpbean_fopo_form_popup';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Form Popup widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'WPB Form Popup', 'wpb-form-popup' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Form Popup widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Form Popup widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the Form Popup widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'form', 'popup', 'wpb' );
	}

	/**
	 * Register Form Popup widget controls.
	 *
	 * Add input fields to allow the user to customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'WPB Form Popup', 'wpb-form-popup' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'shortcode_id',
			array(
				'label'   => esc_html__( 'Select a ShortCode', 'wpb-form-popup' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_all_posts( 'wpbean_fopo_popups' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Form Popup widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();
		if ( $settings['shortcode_id'] && '' !== $settings['shortcode_id'] ) {
			echo '<div class="wpbean-fopo-elementor-widget">';
				echo do_shortcode( '[wpbean-fopo-form-popup id="' . esc_attr( $settings['shortcode_id'] ) . '"]' );
			echo '</div>';
		}
	}
}
