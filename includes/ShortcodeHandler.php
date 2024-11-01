<?php
namespace WpBean\FormPopup;

use function WpBean\FormPopup\enqueue_styles;
use function WpBean\FormPopup\enqueue_scripts;
use function WpBean\FormPopup\get_the_form_plugin_shortcode;
use function WpBean\FormPopup\wpbean_fopo_custom_content_allowed_html;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Shortcode handler class
 */
class ShortcodeHandler {

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_shortcode( 'wpbean-fopo-form-popup', array( $this, 'popup_shortcode' ) );
	}

	/**
	 * ShortCode function
	 *
	 * @param array $atts The shortcode attributes.
	 * @return string
	 */
	public function popup_shortcode( $atts ) {

		$attributes = shortcode_atts(
			array(
				'id' => '',
			),
			$atts
		);

		enqueue_scripts( array( 'wpb-form-popup-hystmodal', 'wpb-form-popup-init' ) );
		enqueue_styles( array( 'wpb-form-popup-style' ) );

		ob_start();

		if ( $attributes['id'] && '' !== $attributes['id'] ) {
			$id                  = $attributes['id'];
			$btn_text            = get_post_meta( $id, 'wpbean_fopo_btn_text', true );
			$btn_size            = get_post_meta( $id, 'wpbean_fopo_btn_size', true );
			$btn_align           = get_post_meta( $id, 'wpbean_fopo_btn_align', true );
			$closeonbg           = get_post_meta( $id, 'wpbean_fopo_popup_allow_outside_click', true );
			$escape_key          = get_post_meta( $id, 'wpbean_fopo_popup_allow_escape_key', true );
			$show_close_icon     = get_post_meta( $id, 'wpbean_fopo_popup_show_close_icon', true );
			$basic_content_style = get_post_meta( $id, 'wpbean_fopo_popup_basic_content_style', true );
			$custom_content      = get_post_meta( $id, 'wpbean_fopo_popup_custom_content', true );

			$popup_wrapper_classes = array(
				'wpbean_fopo_popup_wrapper',
				'wpbean_fopo_popup_wrapper_' . esc_attr( $id ),
			);

			$btn_wrapper_classes = array(
				'wpbean_fopo_btn_wrapper',
				'wpbean_fopo_free_shortcode',
				'wpb_form_popup_button_' . esc_attr( $btn_align ),
			);

			$btn_classes = array(
				'wpbean_fopo_init_popup',
				'wpb_form_popup_button',
				'wpb_form_popup_button_' . esc_attr( $id ),
				'wpb_form_popup_button_' . esc_attr( $btn_size ),
			);

			$popup_body_classes = array(
				'wpbean_fopo_popup_body',
				'hystmodal__window',
				'wpbean_fopo_popup_body_' . esc_attr( $id ),
			);

			if ( 'on' === $basic_content_style ) {
				$popup_body_classes[] = 'entry-content';
			}

			$options_data = array(
				'data-id="' . esc_attr( $id ) . '"',
				'data-closeonbg="' . esc_attr( 'on' === $closeonbg ? 1 : 0 ) . '"',
				'data-escapekey="' . esc_attr( 'on' === $escape_key ? 1 : 0 ) . '"',
				'data-closeicon="' . esc_attr( 'on' === $show_close_icon ? 1 : 0 ) . '"',
				'data-content_style="' . esc_attr( 'on' === $basic_content_style ? 1 : 0 ) . '"',
			);
			?>
			<div class="<?php echo esc_attr( implode( ' ', $popup_wrapper_classes ) ); ?>" <?php echo wp_kses_post( implode( ' ', $options_data ) ); ?>>
				<div class="wpbean_fopo_popup_inner">
					<div class="<?php echo esc_attr( implode( ' ', $btn_wrapper_classes ) ); ?>">
						<button data-id="<?php echo esc_attr( $id ); ?>" data-hystmodal="#wpbean_fopo_popup_body_<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $btn_classes ) ); ?>"><?php echo esc_html( $btn_text ); ?></button>
					</div>
					<div class="hystmodal wpbean_fopo_popup_hystmodal wpbean_fopo_popup_hystmodal_<?php echo esc_attr( $id ); ?>" id="wpbean_fopo_popup_body_<?php echo esc_attr( $id ); ?>" aria-hidden="true">
						<div class="hystmodal__wrap">
							<div class="<?php echo esc_attr( implode( ' ', $popup_body_classes ) ); ?>" role="dialog" aria-modal="true">
								<div class="wpbean_fopo_popup_body_inside">
									<?php echo ( 'on' === $show_close_icon ? '<button data-hystclose class="hystmodal__close">&#215;</button>' : '' ); ?>
									<div class="wpbean_fopo_popup_body_inner">
										<?php echo wp_kses( $custom_content && '' !== $custom_content ? apply_filters( 'the_content', wpautop( $custom_content ) ) : '', wpbean_fopo_custom_content_allowed_html() ); ?>
										<?php echo do_shortcode( get_the_form_plugin_shortcode( esc_attr( $id ) ) ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		} else {
			printf( '<div class="wpbean-fopo-alert wpbean-fopo-alert-error">%s</div>', esc_html__( 'Popup ShortCode id required.', 'wpb-form-popup' ) );
		}

		return ob_get_clean();
	}
}