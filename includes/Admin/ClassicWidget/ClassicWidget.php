<?php
namespace WpBean\FormPopup\Admin\ClassicWidget;

use WP_Widget;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Classic Widget Class.
 */
class ClassicWidget extends WP_Widget {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'wpbean_fopo_classic_widget',
			esc_html__( 'WPB Form Popup (Classic)', 'wpb-form-popup' ),
			array( 'description' => esc_html__( 'WPB form popup classic widget.', 'wpb-form-popup' ) )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Arguments.
	 * @param array $instance Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$id    = isset( $instance['id'] ) ? $instance['id'] : '';

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		echo do_shortcode( '[wpbean-fopo-form-popup id="' . esc_attr( $id ) . '"]' );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Instance.
	 * @return void
	 */
	public function form( $instance ) {

		$shortcodes = get_posts(
			array(
				'post_type'      => 'wpbean_fopo_popups',
				'post_status'    => 'publish',
				'posts_per_page' => '-1',
				'fields'         => 'ids',
			)
		);

		$title = isset( $instance['title'] ) ? sanitize_text_field( $instance['title'] ) : '';
		$id    = isset( $instance['id'] ) ? intval( $instance['id'] ) : '';
		?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'wpb-form-popup' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			</p>
			<p>	
				<?php if ( ! empty( $shortcodes ) && isset( $shortcodes ) ) : ?>
					<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php esc_html_e( 'Select a Popup ShortCode', 'wpb-form-popup' ); ?></label> 
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>">
						<option><?php esc_html_e( 'Select a Popup ShortCode', 'wpb-form-popup' ); ?></option>
						<?php
						foreach ( $shortcodes as $shortcode ) {
							printf( '<option value="%s" %s>%s</option>', esc_attr( $shortcode ), selected( $id, $shortcode, false ), '#' . esc_attr( $shortcode ) . ' ' . esc_html( get_post_meta( $shortcode, 'wpbean_fopo_shortcode_title', true ) ) );
						}
						?>
					</select>
				<?php else : ?>
					<?php printf( '<span>%s</span><span><a href="%s">%s</a></span>', esc_html__( 'First, add a popup ShortCode. ', 'wpb-form-popup' ), esc_url( admin_url( '/admin.php?page=wpbean_fopo_popup_shortcodes', false ) ), esc_html__( 'Go to the popup ShortCode builder', 'wpb-form-popup' ) ); ?>
				<?php endif; ?>
			</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['id']    = ( ! empty( $new_instance['id'] ) ) ? absint( $new_instance['id'] ) : '';

		return $instance;
	}
}