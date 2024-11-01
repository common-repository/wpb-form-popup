<?php
namespace WpBean\FormPopup;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Supported Form Plugins.
 *
 * @return array
 */
function form_plugins() {
	$form_plugins = array(
		array(
			'name' => 'Contact Form 7',
			'slug' => 'contact-form-7',
			'file' => 'contact-form-7/wp-contact-form-7.php',
		),
		array(
			'name' => 'Contact Form by WPForms',
			'slug' => 'wpforms-lite',
			'file' => 'wpforms-lite/wpforms.php',
		),
		array(
			'name' => 'WP User Frontend',
			'slug' => 'wp-user-frontend',
			'file' => 'wp-user-frontend/wpuf.php',
		),
		array(
			'name' => 'Ninja Forms Contact Form',
			'slug' => 'ninja-forms',
			'file' => 'ninja-forms/ninja-forms.php',
		),
		array(
			'name' => 'Forminator',
			'slug' => 'forminator',
			'file' => 'forminator/forminator.php',
		),
		array(
			'name' => 'Formidable Forms',
			'slug' => 'formidable',
			'file' => 'formidable/formidable.php',
		),
		array(
			'name' => 'Form Maker by 10Web',
			'slug' => 'form-maker',
			'file' => 'form-maker/form-maker.php',
		),
		array(
			'name' => 'weForms',
			'slug' => 'weforms',
			'file' => 'weforms/weforms.php',
		),
		array(
			'name' => 'Fluent Forms',
			'slug' => 'fluentform',
			'file' => 'fluentform/fluentform.php',
		),
		array(
			'name' => 'HappyForms',
			'slug' => 'happyforms',
			'file' => 'happyforms/happyforms.php',
		),
		array(
			'name' => 'WS Form',
			'slug' => 'ws-form',
			'file' => 'ws-form/ws-form.php',
		),
		array(
			'name' => 'MC4WP: Mailchimp for WordPress',
			'slug' => 'mailchimp-for-wp',
			'file' => 'mailchimp-for-wp/mailchimp-for-wp.php',
		),
		array(
			'name' => 'User Registration by WPEverest',
			'slug' => 'user-registration',
			'file' => 'user-registration/user-registration.php',
		),
		array(
			'name' => 'Bookly',
			'slug' => 'bookly-responsive-appointment-booking-tool',
			'file' => 'bookly-responsive-appointment-booking-tool/main.php',
		),
		array(
			'name' => 'Everest Forms',
			'slug' => 'everest-forms',
			'file' => 'everest-forms/everest-forms.php',
		),
		array(
			'name' => 'Kali Forms',
			'slug' => 'kali-forms',
			'file' => 'kali-forms/kali-forms.php',
		),
		array(
			'name' => 'Bit Form',
			'slug' => 'bit-form',
			'file' => 'bit-form/bitforms.php',
		),
	);
	return apply_filters( 'wpbean_fopo_supported_form_plugins', $form_plugins );
}

/**
 * Add support for the WPForms Pro.
 */

add_filter( 'wpbean_fopo_supported_form_plugins', function( $form_plugins ){

	if ( is_plugin_active( 'wpforms/wpforms.php' ) ) {
		$form_plugins[] = array(
			'name' => 'Contact Form by WPForms',
			'slug' => 'wpforms',
			'file' => 'wpforms/wpforms.php',
		);
	}

	return $form_plugins;
} );

/**
 * Get The Form Plugin ShortCode
 *
 * @param integer $id The ShortCode ID.
 * @return string
 */
function get_the_form_plugin_shortcode( $id ) {
	$output                     = '';
	$form_plugin                = get_post_meta( $id, 'wpbean_fopo_form_plugin', true );
	$without_form               = get_post_meta( $id, 'wpbean_fopo_without_form_plugin', true );
	$cf7                        = get_post_meta( $id, 'wpbean_fopo_cf7_form', true );
	$wpforms                    = get_post_meta( $id, 'wpbean_fopo_wpforms_form', true );
	$ninja_forms                = get_post_meta( $id, 'wpbean_fopo_ninja_forms_form', true );
	$forminator                 = get_post_meta( $id, 'wpbean_fopo_forminator_form', true );
	$formidable                 = get_post_meta( $id, 'wpbean_fopo_formidable_forms_form', true );
	$form_maker                 = get_post_meta( $id, 'wpbean_fopo_form_maker_form', true );
	$weforms                    = get_post_meta( $id, 'wpbean_fopo_weforms_form', true );
	$fluent_form                = get_post_meta( $id, 'wpbean_fopo_fluent_form', true );
	$fluent_form_conversational = get_post_meta( $id, 'wpbean_fopo_fluent_form_conversational', true );
	$mc4wp_form                 = get_post_meta( $id, 'wpbean_fopo_mc4wp_form', true );
	$happyforms_form            = get_post_meta( $id, 'wpbean_fopo_happyforms_form', true );
	$wsform_form                = get_post_meta( $id, 'wpbean_fopo_wsform_form', true );
	$user_registration_form     = get_post_meta( $id, 'wpbean_fopo_user_registration_form', true );
	$everest_forms_form         = get_post_meta( $id, 'wpbean_fopo_everest_forms_form', true );
	$kaliform_form              = get_post_meta( $id, 'wpbean_fopo_kali_forms_form', true );
	$bitform_form               = get_post_meta( $id, 'wpbean_fopo_bitform_form', true );
	$wpuf                       = get_post_meta( $id, 'wpbean_fopo_wpuf_form', true );

	switch ( $form_plugin ) {
		case 'contact-form-7':
			$output = '[contact-form-7 id="' . $cf7 . '"]';
			break;

		case 'contact-form-by-wpforms':
			$output = '[wpforms id="' . $wpforms . '"]';
			break;

		case 'wp-user-frontend':
			$output = '[wpuf_form id="' . $wpuf . '"]';
			break;

		case 'ninja-forms-contact-form':
			$output = '[ninja_form id="' . $ninja_forms . '"]';
			break;

		case 'forminator':
			$output = '[forminator_form id="' . $forminator . '"]';
			break;

		case 'formidable-forms':
			$output = '[formidable id="' . $formidable . '"]';
			break;

		case 'form-maker-by-10web':
			$output = '[Form id="' . $form_maker . '"]';
			break;

		case 'weforms':
			$output = '[weforms id="' . $weforms . '"]';
			break;

		case 'fluent-forms':
			if ( 'on' === $fluent_form_conversational ) {
				$output = '[fluentform type="conversational" id="' . $fluent_form . '"]';
			} else {
				$output = '[fluentform id="' . $fluent_form . '"]';
			}
			break;

		case 'mc4wp-mailchimp-for-wordpress':
			$output = '[mc4wp_form id="' . $mc4wp_form . '"]';
			break;

		case 'happyforms':
			$output = '[form id="' . $happyforms_form . '"]';
			break;

		case 'ws-form':
			$output = '[ws_form id="' . $wsform_form . '"]';
			break;

		case 'user-registration-by-wpeverest':
			$output = '[user_registration_form id="' . $user_registration_form . '"]';
			break;

		case 'bookly':
			$output = '[bookly-form]';
			break;

		case 'everest-forms':
			$output = '[everest_form id="' . $everest_forms_form . '"]';
			break;

		case 'kali-forms':
			$output = '[kaliform id="' . $kaliform_form . '"]';
			break;

		case 'bit-form':
			$output = '[bitform id="' . $bitform_form . '"]';
			break;

		default:
			if ( 'on' !== $without_form ) {
				$output = sprintf( '<div class="wpbean-fopo-alert wpbean-fopo-alert-error">%s</div>', esc_html__( 'No Forms Selected. Please pick a form in the popup shortcode maker.', 'wpb-form-popup' ) );
			}
			break;
	}

	return apply_filters( 'wpbean_fopo_get_the_form_plugin_shortcode', $output, $form_plugin, $id );
}

/**
 * Custom content allowed HTML tags
 *
 * @return array
 */
function wpbean_fopo_custom_content_allowed_html() {
	$allowed_html = array(
		'form'   => array(
			'class'          => true,
			'id'             => true,
			'action'         => true,
			'accept'         => true,
			'accept-charset' => true,
			'enctype'        => true,
			'method'         => true,
			'name'           => true,
			'target'         => true,
			'aria-label'     => true,
		),

		'input'  => array(
			'size'          => true,
			'class'         => true,
			'id'            => true,
			'autocomplete'  => true,
			'aria-required' => true,
			'aria-invalid'  => true,
			'type'          => true,
			'name'          => true,
			'value'         => true,
			'checked'       => true,
			'placeholder'   => true,
		),

		'button' => array(
			'type'  => true,
			'class' => true,
			'id'    => true,
			'label' => true,
		),

		'svg'    => array(
			'hidden'    => true,
			'role'      => true,
			'focusable' => true,
			'xmlns'     => true,
			'width'     => true,
			'height'    => true,
			'viewbox'   => true,
		),

		'path'   => array(
			'd' => true,
		),
	);

	return array_merge( wp_kses_allowed_html( 'post' ), $allowed_html );
}

/**
 * Calling the registered scripts
 *
 * @param array $assets An array of the registered scripts to enqueue.
 * @return void
 */
function enqueue_scripts( $assets ) {
	if ( $assets && ! empty( $assets ) ) {
		foreach ( $assets as $asset ) {
			wp_enqueue_script( $asset );
		}
	}
}

/**
 * Calling the registered styles
 *
 * @param array $assets An array of the registered styles to enqueue.
 * @return void
 */
function enqueue_styles( $assets ) {
	if ( $assets && ! empty( $assets ) ) {
		foreach ( $assets as $asset ) {
			wp_enqueue_style( $asset );
		}
	}
}

/**
 * Get all the image sizes for meta fields.
 *
 * @return array
 */
function get_image_sizes() {
	$sizes   = get_intermediate_image_sizes();
	$sizes[] = 'full';
	$output  = array();
	if ( ! empty( $sizes ) ) {
		foreach ( $sizes as $size ) {
			$output[ $size ] = $size;
		}
	}
	return $output;
}

/**
 * Supported Form Plugins for meta fields.
 *
 * @return array
 */
function form_plugins_meta_fields() {
	$output = array( '' => esc_html__( 'Select a Form', 'wpb-form-popup' ) );
	foreach ( form_plugins() as $plugin ) {
		if ( is_plugin_active( $plugin['file'] ) ) {
			$output[ sanitize_title( $plugin['name'] ) ] = $plugin['name'];
		}
	}
	return $output;
}

/**
 * Ninga Forms for meta fields.
 *
 * @return array
 */
function ninja_forms_meta_fields() {

	global $wpdb;

	$output     = array();
	$key_column = 'id';

	$forms = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT id, title
				FROM {$wpdb->prefix}nf3_forms
				ORDER BY %s ASC
			",
			$key_column
		),
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
		OBJECT
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form->id ] = $form->title;
		}
	}

	return $output;
}

/**
 * Forminator Forms for meta fields.
 *
 * @return array
 */
function forminator_forms_meta_fields() {

	$output = array();
	$forms  = get_posts(
		array(
			'post_type'   => 'forminator_forms',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form ] = htmlspecialchars( forminator_get_form_name( $form ) );
		}
	}

	return $output;
}

/**
 * Formidable Forms for meta fields
 *
 * @return array
 */
function formidable_forms_meta_fields() {

	global $wpdb;

	$output     = array();
	$key_column = 'id';

	$forms = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT id, name
				FROM {$wpdb->prefix}frm_forms
				ORDER BY %s ASC
			",
			$key_column
		),
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
		OBJECT
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form->id ] = $form->name;
		}
	}

	return $output;
}

/**
 * Form Maker by 10Web forms for meta fields.
 *
 * @return array
 */
function formmaker_forms_meta_fields() {

	global $wpdb;

	$output     = array();
	$key_column = 'id';

	$forms = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT id, title
				FROM {$wpdb->prefix}formmaker
				ORDER BY %s ASC
			",
			$key_column
		),
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
		OBJECT
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form->id ] = $form->title;
		}
	}

	return $output;
}

/**
 * Fluent Forms plugin forms for meta fields
 *
 * @return array
 */
function fluent_forms_meta_fields() {

	global $wpdb;

	$output     = array();
	$key_column = 'id';

	$forms = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT id, title
				FROM {$wpdb->prefix}fluentform_forms
				ORDER BY %s ASC
			",
			$key_column
		),
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
		OBJECT
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form->id ] = $form->title;
		}
	}

	return $output;
}

/**
 * WS Forms plugin forms for meta fields
 *
 * @return array
 */
function ws_forms_meta_fields() {

	global $wpdb;

	$output     = array();
	$key_column = 'id';

	$forms = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT id, label
				FROM {$wpdb->prefix}wsf_form
				ORDER BY %s ASC
			",
			$key_column
		),
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
		OBJECT
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form->id ] = $form->label;
		}
	}

	return $output;
}

/**
 * BitForm plugin forms for meta fields.
 *
 * @return array
 */
function bitform_meta_fields() {

	global $wpdb;

	$output     = array();
	$key_column = 'id';

	$forms = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT id, form_name
				FROM {$wpdb->prefix}bitforms_form
				ORDER BY %s ASC
			",
			$key_column
		),
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared.
		OBJECT
	);

	if ( isset( $forms ) && ! empty( $forms ) ) {
		foreach ( $forms as $form ) {
			$output[ $form->id ] = $form->form_name;
		}
	}

	return $output;
}

/**
 * Return meta field sanitize callback.
 *
 * @param string $name The meta field name.
 * @param array  $sections The meta field section.
 * @return string
 */
function get_sanitize_callback( $name, $sections ) {
	if ( is_array( $sections ) && isset( $sections ) && ! empty( $sections ) ) {
		foreach ( $sections as $section ) {
			if ( is_array( $section ) && isset( $section ) && ! empty( $section ) ) {
				foreach ( $section as $feild ) {
					if ( 'group' !== $feild['type'] ) {
						if ( $feild['name'] === $name ) {
							return $feild['sanitize_callback'];
						}
					} else {
						foreach ( $feild['options'] as $group_feild ) {
							if ( $group_feild['name'] === $name ) {
								return $group_feild['sanitize_callback'];
							}
						}
					}
				}
			}
		}
	}
}

/**
 * Sanitize select2 field.
 *
 * @param string $meta_value The meta value of the select2 field.
 * @return string
 */
function sanitize_select2_field( $meta_value ) {
	foreach ( (array) $meta_value as $k => $v ) {
		$meta_value[ $k ] = intval( $v );
	}
	return $meta_value;
}

/**
 * Clean variables using sanitize_text_field and wp_kses_post. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $data Data to sanitize.
 * @return string|array
 */
function wpbean_clean( $data ) {
	if ( is_array( $data ) ) {
		return array_map( 'WpBean\FormPopup\wpbean_clean', $data );
	} elseif ( wp_strip_all_tags( $data ) !== $data ) {
		return $data;
	} else {
		return is_scalar( $data ) ? sanitize_text_field( $data ) : $data;
	}
}
