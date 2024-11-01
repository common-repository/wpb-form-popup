<?php
/**
 * Plugin Name:       WPB Form Popup
 * Plugin URI:        https://wpbean.com/
 * Description:       All in one form popup solution for WordPress.
 * Version:           1.2.5
 * Author:            wpbean
 * Author URI:        https://wpbean.com
 * Text Domain:       wpb-form-popup
 * Domain Path:       /languages
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WPB Form Popup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/vendor/autoload.php';

use WpBean\FormPopup\Admin\CustomPostType\CustomPostType;
use WpBean\FormPopup\Admin\AdminPages\ShortCodesPage;
use WpBean\FormPopup\ShortcodeHandler;
use WpBean\FormPopup\Block\BlockEditor;

/**
 * The main plugin class
 */
final class WpBean_Form_Popup {

	/**
	 * Form Popup version.
	 *
	 * @var string
	 */
	public $version = '1.2.5';

	/**
	 * Instance
	 *
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Class Constructor.
	 */
	private function __construct() {
		$this->define_constants();
		add_action( 'plugins_loaded', array( $this, 'plugin_init' ) );
		add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( plugin_basename( __FILE__ ), array( $this, 'plugin_deactivation' ) );
	}

	/**
	 * Initializes instance.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define plugin Constants.
	 */
	public function define_constants() {
		define( 'WPBEAN_FOPO_FREE_VERSION', $this->version );
		define( 'WPBEAN_FOPO_FREE_INIT', plugin_basename( __FILE__ ) );
		define( 'WPBEAN_FOPO_PREMIUM_VERSION_URL', 'https://wpbean.com/downloads/wpb-form-popup-pro/' );
		define( 'WPBEAN_FOPO_ADMIN_PAGE', 'wpbean_fopo_popup_shortcodes' );
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function plugin_init() {
		$this->init_classes();
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'plugin_admin_notices' ) );
		add_action( 'admin_notices', array( $this, 'pro_discount_admin_notice' ) );
		add_action( 'admin_init', array( $this, 'pro_discount_admin_notice_dismissed' ) );
		add_action( 'elementor/widgets/register', array( $this, 'elementor_widget' ) );
		add_action( 'widgets_init', array( $this, 'classic_widgets' ) );
	}

	/**
	 * Pro version discount admin notice.
	 *
	 * @return void
	 */
	public function pro_discount_admin_notice() {
		$user_id     = get_current_user_id();
		$screen      = get_current_screen();
		$dismiss_url = wp_nonce_url(
			add_query_arg( 'wpbean-fopo-pro-discount-admin-notice-dismissed', 'true' ),
			'wpbean_fopo_discount_admin_notice_dismissed',
			'wpbean_fopo_discount_admin_notice_dismissed_nonce'
		);

		if ( ! get_user_meta( $user_id, 'wpbean_fopo_pro_discount_dismissed' ) && ! defined( 'WPBEAN_FOPO_PRO_INIT' ) && 'toplevel_page_' . WPBEAN_FOPO_ADMIN_PAGE !== $screen->base ) {
			printf(
				'<div class="wpbean-fopo-discount-notice wpbean-fopo-notice notice updated is-dismissible"><p>%s <a target="_blank" href="%s">%s</a>! %s <b>%s</b></p><p class="actions"><a href="%s" target="_blank" class="button button-primary">%s</a></p><a href="%s" class="notice-dismiss"></a></div>',
				esc_html__( 'Get a 10% exclusive discount on the premium version of the', 'wpb-form-popup' ),
				esc_url( WPBEAN_FOPO_PREMIUM_VERSION_URL ) . '?utm_content=WPB+Form+Popup+Pro&utm_campaign=adminlink&utm_medium=discount-notie&utm_source=FreeVersion',
				esc_html__( 'WPB Form Popup', 'wpb-form-popup' ),
				esc_html__( 'Use discount code - ', 'wpb-form-popup' ),
				'10PERCENTOFF',
				esc_url( WPBEAN_FOPO_PREMIUM_VERSION_URL ) . '?utm_content=WPB+Form+Popup+Pro&utm_campaign=adminlink&utm_medium=discount-notie&utm_source=FreeVersion',
				esc_html__( 'Get WPB Form Popup Pro', 'wpb-form-popup' ),
				esc_url( $dismiss_url ),
			);
		}
	}

	/**
	 * Initialize the dismissed function
	 *
	 * @return void
	 */
	public function pro_discount_admin_notice_dismissed() {
		$user_id = get_current_user_id();

		if ( ! empty( $_GET['wpbean-fopo-pro-discount-admin-notice-dismissed'] ) ) { // WPCS: input var ok.
			check_admin_referer( 'wpbean_fopo_discount_admin_notice_dismissed', 'wpbean_fopo_discount_admin_notice_dismissed_nonce' );
			add_user_meta( $user_id, 'wpbean_fopo_pro_discount_dismissed', 'true', true );
		}
	}

	/**
	 * Plugin Deactivation
	 *
	 * @return void
	 */
	public function plugin_deactivation() {
		$user_id = get_current_user_id();
		if ( get_user_meta( $user_id, 'wpbean_fopo_pro_discount_dismissed' ) ) {
			delete_user_meta( $user_id, 'wpbean_fopo_pro_discount_dismissed' );
		}

		flush_rewrite_rules();
	}

	/**
	 * Do stuff upon plugin activation.
	 *
	 * @return void
	 */
	public function activate() {

		$installed = get_option( 'wpbean_fopo_installed' );

		if ( ! $installed ) {
			update_option( 'wpbean_fopo_installed', time() );
		}

		update_option( 'wpbean_fopo_lite_version', $this->version );

		$post = array(
			'post_status' => 'publish',
			'post_type'   => 'wpbean_fopo_popups',
			'post_title'  => esc_html__( 'Popup ShortCode', 'wpb-form-popup' ),
		);

		if ( '1' !== get_option( 'wpbean_fopo_created_first_popup_shortcode_post' ) ) {
			wp_insert_post( $post );
			update_option( 'wpbean_fopo_created_first_popup_shortcode_post', true );
		}
	}

	/**
	 * Plugin activation redirect.
	 *
	 * @param string $plugin Path to the plugin file relative to the plugins directory.
	 *
	 * @return void
	 */
	public function activation_redirect( $plugin ) {
		if ( plugin_basename( __FILE__ ) === $plugin ) {
			wp_safe_redirect( esc_url( admin_url( 'admin.php?page=' . WPBEAN_FOPO_ADMIN_PAGE ) ) );
			exit();
		}
	}

	/**
	 * Plugin action links.
	 *
	 * @param array $links Plugin action links.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$pro_url = WPBEAN_FOPO_PREMIUM_VERSION_URL . '?utm_content=WPB+Form+Popup+Pro&utm_campaign=adminlink&utm_medium=action-link&utm_source=FreeVersion';
		$links[] = '<a href="' . admin_url( 'admin.php?page=' . WPBEAN_FOPO_ADMIN_PAGE ) . '">' . esc_html__( 'Settings', 'wpb-form-popup' ) . '</a>';
		$links[] = '<a target="_blank" href="https://docs.wpbean.com/?p=1836">' . esc_html__( 'Documentation', 'wpb-form-popup' ) . '</a>';
		if ( ! defined( 'WPBEAN_FOPO_PRO_INIT' ) ) {
			$links[] = '<a target="_blank" style="color: rgb(0, 163, 42);text-shadow: 1px 1px 1px #eee;font-weight: 700;" href="' . esc_url( $pro_url ) . '">' . esc_html__( 'Get Pro', 'wpb-form-popup' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Initialize the classes.
	 *
	 * @return void
	 */
	public function init_classes() {

		if ( is_admin() ) {
			new CustomPostType();
			new ShortCodesPage();
		}

		if ( ! defined( 'WPBEAN_FOPO_PRO_INIT' ) ) {
			new ShortcodeHandler();
		}

		new BlockEditor();
	}

	/**
	 * Register new Elementor widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function elementor_widget( $widgets_manager ) {
		$widgets_manager->register( new WpBean\FormPopup\Elementor\ElementorWidget() );
	}

	/**
	 * Register Classic Widget
	 *
	 * @return void
	 */
	public function classic_widgets() {
		register_widget( 'WpBean\FormPopup\Admin\ClassicWidget\ClassicWidget' );
	}

	/**
	 * Initialize plugin for localization.
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wpb-form-popup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_style( 'wpb-form-popup-style', plugins_url( 'assets/css/main.css', __FILE__ ), array(), $this->version );
		wp_register_script( 'wpb-form-popup-hystmodal', plugins_url( 'assets/hystmodal/hystmodal.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_register_script( 'wpb-form-popup-init', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), $this->version, true );

		if ( $this->trigger_options() && ! empty( $this->trigger_options() ) ) {
			wp_add_inline_script( 'wpb-form-popup-init', 'const wpbean_fopo_Auto_Triggers = ' . wp_json_encode( $this->trigger_options() ), 'before' );
		}

		// Adding Custom Styles.
		$custom_css = '';
		$shortcodes = get_posts(
			array(
				'post_type'   => 'wpbean_fopo_popups',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		if ( isset( $shortcodes ) && ! empty( $shortcodes ) ) {
			foreach ( $shortcodes as $shortcode ) {

				$btn_spacing_top            = get_post_meta( $shortcode, 'wpbean_fopo_btn_spacing_top', true );
				$btn_spacing_right          = get_post_meta( $shortcode, 'wpbean_fopo_btn_spacing_right', true );
				$btn_spacing_bottom         = get_post_meta( $shortcode, 'wpbean_fopo_btn_spacing_bottom', true );
				$btn_spacing_left           = get_post_meta( $shortcode, 'wpbean_fopo_btn_spacing_left', true );
				$btn_margin_top             = get_post_meta( $shortcode, 'wpbean_fopo_btn_margin_top', true );
				$btn_margin_right           = get_post_meta( $shortcode, 'wpbean_fopo_btn_margin_right', true );
				$btn_margin_bottom          = get_post_meta( $shortcode, 'wpbean_fopo_btn_margin_bottom', true );
				$btn_margin_left            = get_post_meta( $shortcode, 'wpbean_fopo_btn_margin_left', true );
				$btn_border_radius          = get_post_meta( $shortcode, 'wpbean_fopo_btn_border_radius', true );
				$btn_add_border             = get_post_meta( $shortcode, 'wpbean_fopo_btn_add_border', true );
				$btn_border_color           = get_post_meta( $shortcode, 'wpbean_fopo_btn_border_color', true );
				$btn_border_color_hover     = get_post_meta( $shortcode, 'wpbean_fopo_btn_border_color_hover', true );
				$btn_border_width           = get_post_meta( $shortcode, 'wpbean_fopo_btn_border_width', true );
				$btn_border_type            = get_post_meta( $shortcode, 'wpbean_fopo_btn_border_type', true );
				$btn_bg                     = get_post_meta( $shortcode, 'wpbean_fopo_btn_bg', true );
				$bg_hover                   = get_post_meta( $shortcode, 'wpbean_fopo_btn_bg_hover', true );
				$btn_color                  = get_post_meta( $shortcode, 'wpbean_fopo_btn_color', true );
				$btn_color_hover            = get_post_meta( $shortcode, 'wpbean_fopo_btn_color_hover', true );
				$popup_width_number         = get_post_meta( $shortcode, 'wpbean_fopo_popup_width_number', true );
				$popup_width_unit           = get_post_meta( $shortcode, 'wpbean_fopo_popup_width_unit', true );
				$popup_height_number        = get_post_meta( $shortcode, 'wpbean_fopo_popup_height_number', true );
				$popup_height_unit          = get_post_meta( $shortcode, 'wpbean_fopo_popup_height_unit', true );
				$popup_overflow_x           = get_post_meta( $shortcode, 'wpbean_fopo_popup_overflow_x', true );
				$popup_overflow_y           = get_post_meta( $shortcode, 'wpbean_fopo_popup_overflow_y', true );
				$popup_z_index              = get_post_meta( $shortcode, 'wpbean_fopo_popup_z_index', true );
				$overlay_popup_bg           = get_post_meta( $shortcode, 'wpbean_fopo_overlay_popup_bg', true );
				$popup_bg                   = get_post_meta( $shortcode, 'wpbean_fopo_popup_bg', true );
				$popup_color                = get_post_meta( $shortcode, 'wpbean_fopo_popup_color', true );
				$popup_border_radius        = get_post_meta( $shortcode, 'wpbean_fopo_popup_border_radius', true );
				$popup_spacing_top          = get_post_meta( $shortcode, 'wpbean_fopo_popup_spacing_top', true );
				$popup_spacing_right        = get_post_meta( $shortcode, 'wpbean_fopo_popup_spacing_right', true );
				$popup_spacing_bottom       = get_post_meta( $shortcode, 'wpbean_fopo_popup_spacing_bottom', true );
				$popup_spacing_left         = get_post_meta( $shortcode, 'wpbean_fopo_popup_spacing_left', true );
				$close_stroke_color         = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_stroke_color', true );
				$close_fill_bg              = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_fill_bg', true );
				$close_fill_color           = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_fill_color', true );
				$close_outline_color        = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_outline_color', true );
				$close_icon_font_size       = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_icon_font_size', true );
				$close_width                = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_width', true );
				$close_height               = get_post_meta( $shortcode, 'wpbean_fopo_popup_close_height', true );
				$close_btn_position_top     = get_post_meta( $shortcode, 'wpbean_fopo_close_btn_position_top', true );
				$close_btn_position_right   = get_post_meta( $shortcode, 'wpbean_fopo_close_btn_position_right', true );
				$close_btn_position_bottom  = get_post_meta( $shortcode, 'wpbean_fopo_close_btn_position_bottom', true );
				$close_btn_position_left    = get_post_meta( $shortcode, 'wpbean_fopo_close_btn_position_left', true );
				$content_image_width        = get_post_meta( $shortcode, 'wpbean_fopo_popup_content_image_width', true );
				$content_form_width         = get_post_meta( $shortcode, 'wpbean_fopo_popup_content_form_width', true );
				$wpbean_fopo_img_col_bg     = get_post_meta( $shortcode, 'wpbean_fopo_img_col_bg', true );
				$wpbean_fopo_img_col_color  = get_post_meta( $shortcode, 'wpbean_fopo_img_col_color', true );
				$wpbean_fopo_form_col_bg    = get_post_meta( $shortcode, 'wpbean_fopo_form_col_bg', true );
				$wpbean_fopo_form_col_color = get_post_meta( $shortcode, 'wpbean_fopo_form_col_color', true );
				$popup_content_align        = get_post_meta( $shortcode, 'wpbean_fopo_popup_content_align', true );
				$popup_heading_align        = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_align', true );
				$popup_heading_size         = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_size', true );
				$popup_heading_weight       = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_weight', true );
				$heading_spacing_top        = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_spacing_top', true );
				$heading_spacing_right      = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_spacing_right', true );
				$heading_spacing_bottom     = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_spacing_bottom', true );
				$heading_spacing_left       = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_spacing_left', true );
				$heading_margin_top         = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_margin_top', true );
				$heading_margin_right       = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_margin_right', true );
				$heading_margin_bottom      = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_margin_bottom', true );
				$heading_margin_left        = get_post_meta( $shortcode, 'wpbean_fopo_popup_heading_margin_left', true );
				$popup_content_layout       = get_post_meta( $shortcode, 'wpbean_fopo_popup_content_layout', true );

				// Popup Content Layout & Style.
				if ( $wpbean_fopo_img_col_bg ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_content_left_form_right .wpbean_fopo_popup_column_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_form .wpbean_fopo_popup_column_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_form_image .wpbean_fopo_popup_column_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_top_image .wpbean_fopo_popup_content_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_title_top_form .wpbean_fopo_popup_header {
                    background: {$wpbean_fopo_img_col_bg};
                }";
				}

				if ( $wpbean_fopo_img_col_color ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_content_left_form_right .wpbean_fopo_popup_column_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_form .wpbean_fopo_popup_column_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_form_image .wpbean_fopo_popup_column_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_top_image .wpbean_fopo_popup_content_image,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_title_top_form .wpbean_fopo_popup_header,
				.wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_title_top_form .wpbean_fopo_popup_header .wpbean_fopo_popup_heading_title {
                    color: {$wpbean_fopo_img_col_color};
                }";
				}

				if ( $wpbean_fopo_form_col_bg ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_form .wpbean_fopo_popup_column_form,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_form_image .wpbean_fopo_popup_column_form,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_top_image .wpbean_fopo_popup_content_form,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_title_top_form .wpbean_fopo_popup_body_inside {
                    background: {$wpbean_fopo_form_col_bg};
                }";
				}

				if ( $wpbean_fopo_form_col_color ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_content_left_form_right .wpbean_fopo_popup_column_form,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_form .wpbean_fopo_popup_column_form,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_form_image .wpbean_fopo_popup_column_form,
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_top_image .wpbean_fopo_popup_content_form,
				.wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_title_top_form .wpbean_fopo_popup_body_inside {
                    color: {$wpbean_fopo_form_col_color};
                }";
				}

				if ( $popup_content_align && 'default' !== $popup_content_align ) {
					$custom_css .= "
				.wpbean_fopo_popup_body_{$shortcode}.hystmodal__window {
                    text-align: {$popup_content_align};
                }";
				}

				if ( $popup_heading_align && 'default' !== $popup_heading_align ) {
					$custom_css .= "
				.wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_text_wrapper {
                    text-align: {$popup_heading_align};
                }";
				}

				if ( $popup_heading_size ) {
					$custom_css .= "
				.wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_title {
                    font-size: {$popup_heading_size}px;
                }";
				}

				if ( $popup_heading_weight ) {
					$custom_css .= "
				.wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_title {
                    font-weight: {$popup_heading_weight};
                }";
				}

				if ( $heading_spacing_top ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_header {
                    padding-top: {$heading_spacing_top}px;
                }";
				}

				if ( $heading_spacing_right ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_header {
                    padding-right: {$heading_spacing_right}px;
                }";
				}

				if ( $heading_spacing_bottom ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_header {
                    padding-bottom: {$heading_spacing_bottom}px;
                }";
				}

				if ( $heading_spacing_left ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_header {
                    padding-left: {$heading_spacing_left}px;
                }";
				}

				if ( $heading_margin_top ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_text_wrapper {
                    padding-top: {$heading_margin_top}px;
                }";
				}

				if ( $heading_margin_right ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_text_wrapper {
                    padding-right: {$heading_margin_right}px;
                }";
				}

				if ( $heading_margin_bottom ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_text_wrapper {
                    padding-bottom: {$heading_margin_bottom}px;
                }";
				}

				if ( $heading_margin_left ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_heading_text_wrapper {
                    padding-left: {$heading_margin_left}px;
                }";
				}

				if ( $content_image_width ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_column_image {
                    width: {$content_image_width}%;
                }";
				}

				if ( $content_form_width ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_column_form {
                    width: {$content_form_width}%;
                }";
				}

				// Popup Close Icon.
				if ( $close_stroke_color ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_close_color_type_stroke .hystmodal__close {
                    color: {$close_stroke_color};
                }";
				}

				if ( $close_fill_color ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_close_color_type_fill .hystmodal__close {
                    background: {$close_fill_bg};
                    color: {$close_fill_color};
                }";
				}

				if ( $close_outline_color ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close:focus {
                    outline: 2px dotted {$close_outline_color};
                }";
				}

				if ( $close_icon_font_size ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    font-size: {$close_icon_font_size}px;
                }";
				}

				if ( $close_width ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    width: {$close_width}px;
                }";
				}

				if ( $close_height ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    height: {$close_height}px;
                }";
				}

				if ( $close_btn_position_top || '0' === $close_btn_position_top ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    top: {$close_btn_position_top}px;
                }";
				}

				if ( $close_btn_position_right || '0' === $close_btn_position_right ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    right: {$close_btn_position_right}px;
                }";
				}

				if ( $close_btn_position_bottom || '0' === $close_btn_position_bottom ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    bottom: {$close_btn_position_bottom}px;
                }";
				}

				if ( $close_btn_position_left || '0' === $close_btn_position_left ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .hystmodal__close {
                    left: {$close_btn_position_left}px;
                }";
				}

				// Btn Padding.
				if ( $btn_spacing_top ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                    padding-top: {$btn_spacing_top}px;
                }";
				}

				if ( $btn_spacing_right ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                    padding-right: {$btn_spacing_right}px;
                }";
				}

				if ( $btn_spacing_bottom ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                    padding-bottom: {$btn_spacing_bottom}px;
                }";
				}

				if ( $btn_spacing_left ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                    padding-left: {$btn_spacing_left}px;
                }";
				}

				// Btn Margin.
				if ( $btn_margin_top ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image {
                    margin-top: {$btn_margin_top}px;
                }";
				}

				if ( $btn_margin_right ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image {
                    margin-right: {$btn_margin_right}px;
                }";
				}

				if ( $btn_margin_bottom ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image {
                    margin-bottom: {$btn_margin_bottom}px;
                }";
				}

				if ( $btn_margin_left ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image {
                    margin-left: {$btn_margin_left}px;
                }";
				}

				if ( $btn_border_radius ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                    border-radius: {$btn_border_radius}px;
                }";
				}

				// Button background.
				if ( $btn_bg ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                    background: {$btn_bg};
                }";
				}

				if ( $bg_hover ) {
					$custom_css .= "
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button:hover, .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button:focus,
                .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img:hover, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img:focus {
                    background: {$bg_hover};
                }";
				}

				// Button border.
				if ( 'yes' === $btn_add_border ) {

					if ( $btn_border_width ) {
						$custom_css .= "
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                        border-width: {$btn_border_width}px;
                    }";
					}

					if ( $btn_border_type ) {
						$custom_css .= "
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                        border-style: {$btn_border_type};
                    }";
					}

					if ( $btn_border_color ) {
						$custom_css .= "
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img {
                        border-color: {$btn_border_color};
                    }";
					}

					if ( $btn_border_color_hover ) {
						$custom_css .= "
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button:hover, .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button:focus,
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img:hover, .wpbean_fopo_popup_wrapper_{$shortcode} .wpbean_fopo_popup_image img:focus {
                        border-color: {$btn_border_color_hover};
                    }";
					}
				}

				// Button Color.
				if ( $btn_color ) {
					$custom_css .= "
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button {
                        color: {$btn_color};
                    }";
				}

				if ( $btn_color_hover ) {
					$custom_css .= "
                    .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button:hover, .wpbean_fopo_popup_wrapper_{$shortcode} .wpb_form_popup_button:focus {
                        color: {$btn_color_hover};
                    }";
				}

				// Popup Width BG, border radius, padding, align.
				if ( $popup_width_number ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.hystmodal__window {
                    width: {$popup_width_number}{$popup_width_unit};
                }";
				}

				if ( $popup_height_number ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.hystmodal__window {
                    height: {$popup_height_number}{$popup_height_unit};
                }";
				}

				if ( $popup_overflow_x ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.hystmodal__window {
                    overflow-x: {$popup_overflow_x};
                }";
				}

				if ( $popup_overflow_y ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode}.hystmodal__window {
                    overflow-y: {$popup_overflow_y};
                }";
				}

				if ( $overlay_popup_bg ) {
					$custom_css .= "
                .wpbean_fopo_popup_hystmodal_{$shortcode}.hystmodal--active.hystmodal:before {
                    background: {$overlay_popup_bg};
					opacity: 1;
                }";
				}

				if ( $popup_bg ) {
					if ( 'image_content_left_form_right' === $popup_content_layout ) {
						$custom_css .= "
						.wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_content_left_form_right .wpbean_fopo_popup_column_image {
							background: {$popup_bg};
						}";
					} else {
						$custom_css .= "
						.wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_body_inside {
							background: {$popup_bg};
						}";
					}
				}

				if ( $wpbean_fopo_form_col_bg ) {
					$custom_css .= "
				.wpbean_fopo_popup_body_{$shortcode}.wpbean_fopo_content_layout_image_content_left_form_right .wpbean_fopo_popup_body_inside {
                    background: {$wpbean_fopo_form_col_bg};
                }";
				}

				if ( $popup_color ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} {
                    color: {$popup_color};
                }";
				}

				if ( '' !== $popup_border_radius ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_body_inside {
                    border-radius: {$popup_border_radius}px;
                }";
				}

				if ( $popup_spacing_top ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_body_inner {
                    padding-top: {$popup_spacing_top}px;
                }";
				}

				if ( $popup_spacing_right ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_body_inner {
                    padding-right: {$popup_spacing_right}px;
                }";
				}

				if ( $popup_spacing_bottom ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_body_inner {
                    padding-bottom: {$popup_spacing_bottom}px;
                }";
				}

				if ( $popup_spacing_left ) {
					$custom_css .= "
                .wpbean_fopo_popup_body_{$shortcode} .wpbean_fopo_popup_body_inner {
                    padding-left: {$popup_spacing_left}px;
                }";
				}

				if ( $popup_z_index ) {
					$custom_css .= "
				.wpbean_fopo_popup_hystmodal_{$shortcode}.wpbean_fopo_popup_hystmodal {
					z-index: {$popup_z_index}!important;
				}";
				}
			}
		}
		wp_add_inline_style( 'wpb-form-popup-style', $custom_css );
	}

	/**
	 * Admin scripts and styles.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wpb-form-popup-global-admin', plugins_url( 'includes/Admin/assets/css/global-admin.css', __FILE__ ), array(), $this->version );
		wp_register_style( 'wpb-form-popup-jbox', plugins_url( 'includes/Admin/assets/jbox/jBox.min.css', __FILE__ ), array(), $this->version );
		wp_register_style( 'wpb-form-popup-add-shortcode-style', plugins_url( 'includes/Admin/assets/css/add-shortcode-style.css', __FILE__ ), array(), $this->version );
		wp_register_style( 'wpb-form-popup-select2', plugins_url( 'includes/Admin/assets/css/select2.css', __FILE__ ), array(), $this->version );

		wp_register_script( 'wp-color-picker-alpha', plugins_url( 'includes/Admin/assets/js/wp-color-picker-alpha.min.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), $this->version, true );
		wp_register_script( 'wpb-form-popup-select2', plugins_url( 'includes/Admin/assets/js/select2/select2.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_register_script( 'wpb-form-popup-select-togglebutton', plugins_url( 'includes/Admin/assets/js/select-togglebutton.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_register_script( 'wpb-form-popup-jbox', plugins_url( 'includes/Admin/assets/jbox/jBox.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_register_script( 'wpb-form-popup-admin', plugins_url( 'includes/Admin/assets/js/admin.js', __FILE__ ), array( 'jquery', 'wp-util', 'jquery-ui-accordion', 'wp-i18n' ), $this->version, true );

		wp_localize_script(
			'wpb-form-popup-admin',
			'wpbean_fopo_Vars',
			array(
				'_wpbean_fopo_nonce' => wp_create_nonce( 'wpbean-fopo-add-shortcode-nonce' ),
			)
		);

		wp_set_script_translations( 'wpb-form-popup-admin', 'wpb-form-popup' );
	}

	/**
	 * Required plugins URls.
	 *
	 * @return string
	 */
	public function reruired_plugins_links() {
		$output            = '';
		$supported_plugins = WpBean\FormPopup\form_plugins();

		if ( $supported_plugins && ! empty( $supported_plugins ) ) {
			foreach ( $supported_plugins as $supported_plugin ) {
				$output .= sprintf( '<a class="button" href="%1$s" target="_blank">%2$s</a> ', esc_url( 'https://wordpress.org/plugins/' . $supported_plugin['slug'] . '/' ), esc_html( $supported_plugin['name'] ) );
			}
		}

		return $output;
	}

	/**
	 * Plugin admin notices.
	 *
	 * @return void
	 */
	public function plugin_admin_notices() {
		$missing_plugins   = array();
		$supported_plugins = WpBean\FormPopup\form_plugins();

		if ( $supported_plugins && ! empty( $supported_plugins ) ) {
			foreach ( $supported_plugins as $supported_plugin ) {
				if ( ! is_plugin_active( $supported_plugin['file'] ) ) {
					$missing_plugins[] = $supported_plugin;
				}
			}
		}

		if ( count( $missing_plugins ) === count( $supported_plugins ) ) {

			printf(
				'<div class="wpbean-fopo-notice notice notice-error is-dismissible"><p class="wpbean-fopo-notice-message">%1$s <strong>%2$s</strong>%3$s</p><p class="wpbean-fopo-notice-buttons">%4$s</p></div>',
				esc_html__( 'In order to work, the ', 'wpb-form-popup' ),
				esc_html__( 'WPB Form Popup', 'wpb-form-popup' ),
				esc_html__( ' needs at least one of these form plugins to be installed.', 'wpb-form-popup' ),
				wp_kses_post( $this->reruired_plugins_links() ),
			);
		}
	}

	/**
	 * Popup trigger Options
	 *
	 * @return array
	 */
	public function trigger_options() {
		$args = array(
			'post_type'      => 'wpbean_fopo_popups',
			'posts_per_page' => '-1',
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR', // Optional, defaults to "AND".
				array(
					'key'   => 'wpbean_fopo_popup_trigger_type',
					'value' => 'custom_css_class',
				),
				array(
					'key'   => 'wpbean_fopo_popup_trigger_type',
					'value' => 'automatic',
				),
			),
		);

		$posts  = get_posts( $args );
		$output = array();

		if ( $posts && ! empty( $posts ) ) {
			foreach ( $posts as $key => $post ) {
				$popup_trigger_type = get_post_meta( $post, 'wpbean_fopo_popup_trigger_type', true );

				$output[ $key ] = array(
					'id'                  => $post,
					'poopup_trigger_type' => $popup_trigger_type,
				);

				if ( 'custom_css_class' === $popup_trigger_type ) {
					$output[ $key ]['css_classes']      = get_post_meta( $post, 'wpbean_fopo_trigger_css_class', true );
					$output[ $key ]['css_trigger_type'] = get_post_meta( $post, 'wpbean_fopo_popup_css_trigger_type', true );
				} elseif ( 'automatic' === $popup_trigger_type ) {
					$output[ $key ]['automatic_trigger_type']            = get_post_meta( $post, 'wpbean_fopo_popup_automatic_trigger_type', true );
					$output[ $key ]['automatic_trigger_delay']           = get_post_meta( $post, 'wpbean_fopo_popup_page_load_delay', true );
					$output[ $key ]['automatic_trigger_scroll_position'] = get_post_meta( $post, 'wpbean_fopo_popup_scroll_position', true );
					$output[ $key ]['automatic_trigger_save']            = get_post_meta( $post, 'wpbean_fopo_popup_automatic_trigger_save', true );
				}
			}
		}

		return $output;
	}
}

/**
 * Initialize the main plugin.
 *
 * @return \WpBean_Form_Popup
 */

WpBean_Form_Popup::instance();
