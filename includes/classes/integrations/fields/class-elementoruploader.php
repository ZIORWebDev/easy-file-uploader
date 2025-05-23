<?php
/**
 * Elementor Uploader Class
 *
 * This file contains the definition of the Elementor Uploader class, which is responsible
 * for integrating the Easy DragDrop Uploader plugin with Elementor forms.
 *
 * @package    ZIOR\DragDrop
 * @since      1.0.0
 */

namespace ZIOR\DragDrop\Classes\Integrations\Fields;

use ElementorPro\Modules\Forms\Fields\Field_Base;
use Elementor\Controls_Manager;
use ElementorPro\Plugin;
use ElementorPro\Modules\Forms\Classes;
use function ZIOR\DragDrop\Functions\convert_extentions_to_mime_types;
use function ZIOR\DragDrop\Functions\get_allowed_html;
use function ZIOR\DragDrop\Functions\get_default_max_file_size;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Uploader Class
 *
 * This class extends the Elementor Field_Base class and integrates the Easy DragDrop Uploader plugin with Elementor forms.
 *
 * @package    ZIOR\DragDrop
 * @since      1.0.0
 */
class ElementorUploader extends Field_Base {

	/**
	 * Retrieves easy dragdrop fields from the given field array and sets the 'attachment_type'.
	 *
	 * This function filters the provided fields array to return only those with
	 * 'field_type' set to 'easy-dragdrop-upload'. Additionally, it assigns the value
	 * of 'easy_dragdrop_attachment_type' to 'attachment_type' if it exists.
	 *
	 * @param array $fields The array of form fields.
	 * @return array The filtered array containing only DragDrop fields with updated 'attachment_type'.
	 */
	private function get_option_setting_fields( array $fields ): array {
		$setting_fields = array();

		foreach ( $fields as $field ) {
			// Ensure the field is a dragdrop upload field.
			if ( ! isset( $field['field_type'] ) || 'easy-dragdrop-upload' !== $field['field_type'] ) {
				$setting_fields[] = $field;

				continue;
			}

			// Set 'attachment_type' to 'easy_dragdrop_attachment_type' if it exists.
			if ( isset( $field['easy_dragdrop_attachment_type'] ) ) {
				$field['attachment_type'] = $field['easy_dragdrop_attachment_type'];
			}

			$dragdrop_fields[] = $field;
		}

		return $setting_fields;
	}

	/**
	 * Constructor.
	 *
	 * Hooks into WordPress to enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'easy_dragdrop_processed_files', array( $this, 'process_easy_dragdrop_files' ), 10, 4 );
	}

	/**
	 * Process the easy_dragdrop_upload field.
	 *
	 * @since 1.0.0
	 * @param string $field_id The field ID.
	 * @param array  $value_paths The field data.
	 * @param array  $value_urls The field data.
	 * @param mixed  $record The form record instance.
	 */
	public function process_easy_dragdrop_files( string $field_id, array $value_paths, array $value_urls, mixed $record ) {
		// Store updated values in the record.
		if ( $record ) {
			$record->update_field( $field_id, 'value', implode( ', ', $value_urls ) );
			$record->update_field( $field_id, 'raw_value', implode( ', ', $value_paths ) );
		}
	}

	/**
	 * Creates an array of upload sizes based on server limits to use in the file_sizes control.
	 *
	 * @since 1.0.0
	 * @return array The array of upload sizes.
	 */
	private function get_upload_file_size_options() {
		$max_file_size = wp_max_upload_size() / pow( 1024, 2 );

		$sizes = array();

		for ( $file_size = 1; $file_size <= $max_file_size; $file_size++ ) {
			$sizes[ $file_size ] = $file_size . 'MB';
		}

		return $sizes;
	}

	/**
	 * Gets the field type identifier for Elementor.
	 *
	 * @since 1.0.0
	 * @return string The field type slug ('easy-dragdrop-upload').
	 */
	public function get_type() {
		return 'easy-dragdrop-upload';
	}

	/**
	 * Retrieves the display name of the field.
	 *
	 * @since 1.0.0
	 * @return string The translatable name of the field ('DragDrop Upload').
	 */
	public function get_name() {
		return esc_html__( 'DragDrop Upload', 'easy-file-uploader' );
	}

	/**
	 * Updates Elementor form controls to include DragDrop-specific settings.
	 *
	 * Adds custom settings such as max file size, allowed file types, multiple uploads, and max files.
	 *
	 * @since 1.0.0
	 * @param Widget_Base $widget The Elementor widget instance.
	 */
	public function update_controls( $widget ) {
		$elementor    = Plugin::elementor();
		$control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

		if ( is_wp_error( $control_data ) ) {
			return;
		}

		$default_max_file_size = get_default_max_file_size();
		$field_controls        = array(
			'easy_dragdrop_max_file_size'         => array(
				'name'         => 'easy_dragdrop_max_file_size',
				'label'        => esc_html__( 'Max. File Size', 'easy-file-uploader' ),
				'type'         => Controls_Manager::SELECT,
				'condition'    => array(
					'field_type' => $this->get_type(),
				),
				'default'      => get_option( 'easy_dragdrop_max_file_size', $default_max_file_size ),
				'options'      => $this->get_upload_file_size_options(),
				'description'  => esc_html__( 'If you need to increase max upload size please contact your hosting.', 'easy-file-uploader' ),
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			),
			'easy_dragdrop_file_types'            => array(
				'name'         => 'easy_dragdrop_file_types',
				'label'        => esc_html__( 'Allowed File Types', 'easy-file-uploader' ),
				'label_block'  => true,
				'type'         => Controls_Manager::TEXT,
				'default'      => get_option( 'easy_dragdrop_file_types_allowed', '' ),
				'ai'           => array(
					'active' => false,
				),
				'condition'    => array(
					'field_type' => $this->get_type(),
				),
				'description'  => esc_html__( 'Enter the allowed file types, separated by a comma (jpg, gif, pdf, etc).', 'easy-file-uploader' ),
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			),
			'easy_dragdrop_allow_multiple_upload' => array(
				'name'         => 'easy_dragdrop_allow_multiple_upload',
				'label'        => esc_html__( 'Multiple Files', 'easy-file-uploader' ),
				'type'         => Controls_Manager::SWITCHER,
				'condition'    => array(
					'field_type' => $this->get_type(),
				),
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			),
			'easy_dragdrop_max_files'             => array(
				'name'         => 'easy_dragdrop_max_files',
				'label'        => esc_html__( 'Max. Files', 'easy-file-uploader' ),
				'type'         => Controls_Manager::NUMBER,
				'condition'    => array(
					'field_type'                          => $this->get_type(),
					'easy_dragdrop_allow_multiple_upload' => 'yes',
				),
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			),
		);

		$control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );

		$widget->update_control( 'form_fields', $control_data );
	}

	/**
	 * Render the file upload input field.
	 *
	 * @since 1.0.0
	 * @param array  $item        The field settings array.
	 * @param int    $item_index  The index of the field in the form.
	 * @param object $form        The form object responsible for rendering.
	 */
	public function render( $item, $item_index, $form ) {
		// Add base attributes for the file upload field.
		$form->add_render_attribute( 'input' . $item_index, 'class', 'easy-dragdrop-upload' );
		$form->add_render_attribute( 'input' . $item_index, 'type', 'file', true );

		// Handle multiple file uploads.
		if ( ! empty( $item['easy_dragdrop_allow_multiple_upload'] ) ) {
			$form->add_render_attribute( 'input' . $item_index, 'multiple', 'multiple' );
			$form->add_render_attribute(
				'input' . $item_index,
				'name',
				$form->get_attribute_name( $item ) . '[]',
				true
			);
		}

		$file_types   = convert_extentions_to_mime_types( $item['easy_dragdrop_file_types'] );
		$default_size = wp_max_upload_size() / 1024 / 1024;
		$attributes   = array(
			'data-filesize'  => esc_attr( $item['easy_dragdrop_max_file_size'] ?? $default_size ),
			'data-filetypes' => esc_attr( ! empty( $file_types ) ? implode( ',', $file_types ) : '' ),
			'data-label'     => esc_attr( $item['field_label'] ?? '' ),
			'data-maxfiles'  => esc_attr( $item['easy_dragdrop_max_files'] ?? '' ),
		);

		$form->add_render_attribute( 'input' . $item_index, $attributes );

		$input_attributes = $form->get_render_attribute_string( 'input' . $item_index );

		// Allow developers to modify the input attributes.
		do_action( 'easy_dragdrop_before_render_input', $input_attributes );

		$allowed_html = get_allowed_html();

		echo wp_kses( '<input ' . $input_attributes . '>', $allowed_html );
	}

	/**
	 * Validates the uploaded file field.
	 *
	 * Checks whether the required file has been uploaded and adds an error message if missing.
	 *
	 * @since 1.0.0
	 * @param array                $field The field data.
	 * @param Classes\Form_Record  $record The form record instance.
	 * @param Classes\Ajax_Handler $ajax_handler The AJAX handler instance.
	 */
	public function validation( $field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler ) {
		// is the file required and missing?
		if ( $field['required'] && empty( $field['value'] ) ) {
			$ajax_handler->add_error( $field['id'], __( 'Upload a valid file', 'easy-file-uploader' ) );

			return;
		}

		return true;
	}

	/**
	 * Processes a form field.
	 *
	 * This function allows other developers to hook into the field processing
	 * using the `easy_dragdrop_process_field` action.
	 *
	 * @since 1.0.0
	 * @param mixed                $field        The field data to process.
	 * @param Classes\Form_Record  $record       The form record instance.
	 * @param Classes\Ajax_Handler $ajax_handler The AJAX handler instance.
	 */
	public function process_field( $field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler ) {
		$raw_values = ! is_array( $field['raw_value'] ) ? array( $field['raw_value'] ) : $field['raw_value'];

		if ( empty( $raw_values[0] ) ) {
			return;
		}

		// Allow other developers to process the field values.
		do_action( 'easy_dragdrop_process_field', $field['id'], $raw_values, $record );
	}
}
