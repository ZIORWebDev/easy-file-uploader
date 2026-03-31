<?php
/**
 * CF7 Uploader Class
 *
 * This file contains the definition of the CF7 Uploader class, which is responsible
 * for integrating the Easy DragDrop Uploader plugin with CF7 forms.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */

namespace ZIORWebDev\DragDrop\Integrations\Fields;

use WPCF7_FormTag;
use WPCF7_Submission;
use WPCF7_TagGenerator;
use WPCF7_TagGeneratorGenerator;
use WPCF7_MailTag;
use ZIORWebDev\DragDrop\Helpers;

/**
 * CF7 Uploader Class
 *
 * This class extends the Elementor Field_Base class and integrates the Easy DragDrop Uploader plugin with Elementor forms.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */
class CF7Uploader {

	/**
	 * Contains instance or null
	 *
	 * @var object|null
	 */
	private static $instance = null;

	/**
	 * Get custom option value
	 *
	 * @since 1.0.0
	 * @param WPCF7_FormTag $tag Form tag object.
	 * @param string        $option_name Option name.
	 * @return string|null
	 */
	private function get_easy_dragdrop_value( $tag, $option_name ) {
		if ( ! $tag instanceof WPCF7_FormTag ) {
			return null;
		}

		foreach ( $tag->options as $option ) {
			if ( strpos( $option, $option_name . ':' ) === 0 ) {
				$parts = explode( ':', $option, 2 );
				return $parts[1] ?? null;
			}
		}

		return null;
	}

	/**
	 * Returns instance of Settings.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * Hooks into WordPress to add the plugin's settings page and register settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'wpcf7_swv_create_schema', array( $this, 'add_validation_rules' ), 10, 2 );
		add_action( 'wpcf7_admin_init', array( $this, 'register_tag_generator' ), 50 );

		add_filter( 'wpcf7_mail_tag_replaced_easy_dragdrop_upload', array( $this, 'replace_easy_dragdrop_upload_mail_tag' ), 10, 4 );
		add_filter( 'wpcf7_mail_tag_replaced_easy_dragdrop_upload*', array( $this, 'replace_easy_dragdrop_upload_mail_tag' ), 10, 4 );
		add_filter( 'wpcf7_posted_data', array( $this, 'set_posted_data' ), 20 );
	}

	/**
	 * Process the easy_dragdrop_upload field.
	 *
	 * @since 1.0.0
	 * @param array $posted_data The posted form submission data to be processed.
	 * @return array The processed posted data.
	 */
	public function set_posted_data( array $posted_data ): array {
		$files = $posted_data['form_fields'];

		foreach ( $files as $key => $file ) {
			// Allow other developers to process the field values.
			$value = ! is_array( $file ) ? array( $file ) : $file;
			$field = array(
				'id'        => $key,
				'value'     => $value,
				'raw_value' => $value,
			);

			$posted_data[ $key ] = apply_filters( 'easy_dragdrop_process_field', $field );
		}

		return $posted_data;
	}

	/**
	 * Register the easy_dragdrop_upload form tag.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		wpcf7_add_form_tag(
			array( 'easy_dragdrop_upload', 'easy_dragdrop_upload*' ),
			array( $this, 'render_easy_dragdrop_upload_form_tag' ),
			array(
				'name-attr' => true,
			)
		);
	}

	/**
	 * Handler for the file tag.
	 *
	 * @param WPCF7_FormTag $tag Form tag object.
	 * @return string
	 */
	public function render_easy_dragdrop_upload_form_tag( $tag ) {
		if ( empty( $tag->name ) ) {
			return '';
		}

		$validation_error = wpcf7_get_validation_error( $tag->name );
		$class            = wpcf7_form_controls_class( $tag->type );

		if ( $validation_error ) {
			$class .= ' wpcf7-not-valid';
		}

		$raw_file_types = $this->get_easy_dragdrop_value( $tag, 'filetypes' );
		$raw_file_types = array_map( 'trim', explode( '|', $raw_file_types ) );
		$file_types     = Helpers::convert_extentions_to_mime_types( implode( ',', $raw_file_types ) );
		$default_size   = wp_max_upload_size() / 1024 / 1024;
		$file_size      = $tag->get_limit_option();
		$multiple       = $this->get_easy_dragdrop_value( $tag, 'multifiles' );
		$button_label   = $this->get_easy_dragdrop_value( $tag, 'buttonlabel' ) ?? get_option( 'easy_dragdrop_button_label', 'Browse Files' );

		// Replace underscores with spaces. In the field option, we use underscores to separate the words since CF7 doesn't support spaces on field attribute value.
		$button_label = str_replace( '_', ' ', $button_label );

		$atts = array(
			'size'           => $tag->get_size_option( '40' ),
			'class'          => 'easy-dragdrop-upload ' . $tag->get_class_option( $class ),
			'id'             => $tag->get_id_option(),
			'capture'        => $tag->get_option( 'capture', '(user|environment)', true ),
			'tabindex'       => $tag->get_option( 'tabindex', 'signed_int', true ),
			'type'           => 'file',
			'name'           => 'form_fields[' . $tag->name . ']' . ( $multiple ? '[]' : '' ),
			'aria-invalid'   => $validation_error ? 'true' : 'false',
			'data-filesize'  => esc_attr( $file_size ?? $default_size ),
			'data-filetypes' => esc_attr( ! empty( $file_types ) ? implode( ',', $file_types ) : '' ),
			'data-label'     => esc_attr( $button_label ),
			'data-maxfiles'  => esc_attr( $this->get_easy_dragdrop_value( $tag, 'maxfiles' ) ?? '' ),
		);

		if ( $multiple ) {
			$atts['multiple'] = 'multiple';
		}

		if ( $tag->is_required() ) {
			$atts['aria-required'] = 'true';
		}

		if ( $validation_error ) {
			$atts['aria-describedby'] = wpcf7_get_validation_error_reference( $tag->name );
		}

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s />%3$s</span>',
			esc_attr( $tag->name ),
			wpcf7_format_atts( $atts ),
			$validation_error
		);

		return $html;
	}

	/**
	 * Add validation rules for file field.
	 *
	 * @since 1.0.0
	 * @param WPCF7_SWV_Schema  $schema Schema object.
	 * @param WPCF7_ContactForm $contact_form Contact form object.
	 */
	public function add_validation_rules( $schema, $contact_form ) {
		$tags = $contact_form->scan_form_tags(
			array(
				'basetype' => array( 'file' ),
			)
		);

		foreach ( $tags as $tag ) {
			if ( $tag->is_required() ) {
				$schema->add_rule(
					wpcf7_swv_create_rule(
						'requiredfile',
						array(
							'field' => $tag->name,
							'error' => wpcf7_get_message( 'invalid_required' ),
						)
					)
				);
			}

			$schema->add_rule(
				wpcf7_swv_create_rule(
					'file',
					array(
						'field'  => $tag->name,
						'accept' => explode( ',', wpcf7_acceptable_filetypes( $tag->get_option( 'filetypes' ), 'attr' ) ),
						'error'  => wpcf7_get_message( 'upload_easy_dragdrop_upload_type_invalid' ),
					)
				)
			);

			$schema->add_rule(
				wpcf7_swv_create_rule(
					'maxfilesize',
					array(
						'field'     => $tag->name,
						'threshold' => $tag->get_limit_option(),
						'error'     => wpcf7_get_message( 'upload_easy_dragdrop_upload_too_large' ),
					)
				)
			);
		}
	}

	/**
	 * Replace mail tag with uploaded file name.
	 *
	 * @since 1.0.0
	 * @param string            $replaced Replaced value.
	 * @param string|array|null $submitted Submitted value.
	 * @param string            $html HTML.
	 * @param WPCF7_MailTag     $mail_tag Mail tag.
	 * @return string
	 */
	public function replace_easy_dragdrop_upload_mail_tag( $replaced, $submitted, $html, $mail_tag ) {
		$submission  = WPCF7_Submission::get_instance();
		$posted_data = $submission->get_posted_data();
		$name        = $mail_tag->field_name();

		$replaced = wpcf7_flat_join(
			$posted_data[ $name ],
			array(
				'separator' => wp_get_list_item_separator(),
			)
		);

		return $replaced;
	}

	/**
	 * Register tag generator in admin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_tag_generator() {
		$tag_generator = WPCF7_TagGenerator::get_instance();

		$tag_generator->add(
			'easy_dragdrop_upload',
			__( 'DragDrop Upload', 'easy-file-uploader' ),
			array( $this, 'render_tag_generator' ),
			array( 'version' => '2' )
		);
	}

	/**
	 * Render tag generator UI.
	 *
	 * @since 1.0.0
	 * @param WPCF7_ContactForm $contact_form Contact form.
	 * @param array             $options Options.
	 */
	public function render_tag_generator( $contact_form, $options ) {
		$field_types = array(
			'easy_dragdrop_upload' => array(
				'display_name' => __( 'DragDrop File Uploader', 'easy-file-uploader' ),
				'heading'      => __( 'File uploading field form-tag generator', 'easy-file-uploader' ),
				'description'  => __( 'Generates a form-tag for a <a href="https://contactform7.com/file-uploading-and-attachment/">file uploading field</a>.', 'easy-file-uploader' ),
			),
		);

		$tgg  = new WPCF7_TagGeneratorGenerator( $options['content'] );
		$args = array(
			'field_types' => $field_types,
			'tgg'         => $tgg,
		);

		load_template( ZIORWEBDEV_DRAGDROP_PLUGIN_DIR . 'views/cf7-field.php', false, $args );
	}
}
