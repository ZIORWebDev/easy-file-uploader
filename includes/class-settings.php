<?php
/**
 * Settings Class
 *
 * This file contains the definition of the Settings class, which is responsible
 * for handling the settings page and functionality for the Easy DragDrop Uploader plugin.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */

namespace ZIORWebDev\DragDrop;
use ZIORWebDev\DragDrop\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 *
 * This class handles the settings page and functionality for the Easy DragDrop Uploader plugin.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */
class Settings {

	/**
	 * Loads a template file using WordPress's load_template function.
	 *
	 * This function checks if the template file exists before including it.
	 * It also extracts the provided data array into separate variables
	 * to be accessible inside the template.
	 *
	 * @since 1.0.0
	 * @param string $template_name Template file name (without .php extension).
	 * @param array  $data          Optional. Data to pass to the template. Default empty array.
	 *
	 * @return void
	 */
	private function load_template( string $template_name, array $data = array() ): void {
		$template_file = ZIORWEBDEV_DRAGDROP_PLUGIN_DIR . sprintf( 'views/%s.php', $template_name );

		if ( ! file_exists( $template_file ) ) {
			return;
		}

		// Load the template and pass data.
		load_template( $template_file, false, $data );
	}

	/**
	 * Retrieves the settings fields for the plugin.
	 *
	 * @since 1.0.0
	 * @return array The settings fields.
	 */
	private function get_settings_fields(): array {
		$settings_fields = array(
			array(
				'id'       => 'easy_dragdrop_max_file_size',
				'title'    => __( 'Max. File Size', 'easy-file-uploader' ),
				'callback' => array( $this, 'max_file_size_callback' ),
				'section'  => 'easy_dragdrop_general_section',
			),
			array(
				'id'       => 'easy_dragdrop_button_label',
				'title'    => __( 'Default Button Label', 'easy-file-uploader' ),
				'callback' => array( $this, 'button_label_callback' ),
				'section'  => 'easy_dragdrop_general_section',
			),
			array(
				'id'       => 'easy_dragdrop_file_types_allowed',
				'title'    => __( 'Default File Types Allowed', 'easy-file-uploader' ),
				'callback' => array( $this, 'file_types_allowed_callback' ),
				'section'  => 'easy_dragdrop_general_section',
			),
			array(
				'id'       => 'easy_dragdrop_file_type_error',
				'title'    => __( 'File Type Error Message', 'easy-file-uploader' ),
				'callback' => array( $this, 'file_type_error_message_callback' ),
				'section'  => 'easy_dragdrop_general_section',
			),
			array(
				'id'       => 'easy_dragdrop_file_size_error',
				'title'    => __( 'File Size Error Message', 'easy-file-uploader' ),
				'callback' => array( $this, 'file_size_error_message_callback' ),
				'section'  => 'easy_dragdrop_general_section',
			),
		);

		return apply_filters( 'easy_dragdrop_settings_fields', $settings_fields );
	}

	/**
	 * Returns the settings sections for the plugin.
	 *
	 * @since 1.0.0
	 * @return array The settings sections.
	 */
	private function get_settings_sections(): array {
		$settings_sections = array(
			'easy_dragdrop_general_section' => array(
				'title'    => __( 'General Settings', 'easy-file-uploader' ),
				'callback' => array( $this, 'section_callback' ),
			),
		);

		return apply_filters( 'easy_dragdrop_settings_sections', $settings_sections );
	}

	/**
	 * Class constructor.
	 *
	 * Hooks into WordPress to add the plugin's settings page and register settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'easy_dragdrop_settings_after', array( $this, 'render_marketing_card' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Adds a "Settings" link on the plugins page.
	 *
	 * @since 1.0.0
	 * @param array $links The existing action links.
	 * @return array Modified action links with the "Settings" link.
	 */
	public function add_settings_link( array $links ): array {
		// Define the settings link URL.
		$settings_url  = admin_url( 'options-general.php?page=easy-file-uploader' );
		$settings_link = sprintf( '<a href="%s">', $settings_url ) . esc_html__( 'Settings', 'easy-file-uploader' ) . '</a>';

		// Prepend the settings link to the existing links.
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Adds the settings page under the WordPress "Settings" menu.
	 *
	 * This function registers a submenu page under "Settings" in the WordPress admin dashboard.
	 * Only users with the `manage_options` capability can access the settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_settings_page(): void {
		add_menu_page(
			__( 'Easy DragDrop File Uploader', 'easy-file-uploader' ),
			__( 'Easy File Uploader', 'easy-file-uploader' ),
			'manage_options',
			'easy-file-uploader',
			array( $this, 'render_settings_page' ),
			'dashicons-upload',
			65
		);

		// Add "Settings" as a submenu.
		add_submenu_page(
			'easy-file-uploader',
			__( 'Settings', 'easy-file-uploader' ),
			__( 'Settings', 'easy-file-uploader' ),
			'manage_options',
			'easy-file-uploader-settings',
			array( $this, 'render_settings_page' )
		);

		remove_submenu_page( 'easy-file-uploader', 'easy-file-uploader' );
	}

	/**
	 * Registers settings, sections, and fields for the plugin.
	 *
	 * This function registers settings with WordPress, adds a settings section,
	 * and defines various fields for user configuration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings(): void {
		$options = Helpers::get_options();

		foreach ( $options as $option ) {
			register_setting(
				sanitize_text_field( $option['option_group'] ),
				sanitize_text_field( $option['option_name'] ),
				array(
					'sanitize_callback' => 'sanitize_text_field',
					'type'              => sanitize_text_field( $option['type'] ),
				),
			);
		}

		// Add each settings section.
		$sections = $this->get_settings_sections();
		$fields   = $this->get_settings_fields();

		foreach ( $sections as $section_id => $section ) {
			add_settings_section(
				$section_id,
				$section['title'],
				$section['callback'],
				'easy-file-uploader'
			);

			foreach ( $fields as $field ) {
				if ( $field['section'] !== $section_id ) {
					continue;
				}

				add_settings_field(
					$field['id'],
					$field['title'],
					$field['callback'],
					'easy-file-uploader',
					$field['section']
				);
			}
		}
	}

	/**
	 * Renders the marketing card template.
	 *
	 * This function loads the marketing card template file from the 'pro/views' directory.
	 * It uses load_template() to ensure proper inclusion within the WordPress ecosystem.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_marketing_card(): void {
		load_template( ZIORWEBDEV_DRAGDROP_PLUGIN_DIR . 'views/marketing.php', false );
	}

	/**
	 * Renders the settings page.
	 *
	 * This function loads the settings template and provides necessary data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page(): void {
		// Data to pass to the template.
		$data = array(
			'options_group' => 'easy_dragdrop_options_group',
			'page_slug'     => 'easy-file-uploader',
		);

		$this->load_template( 'settings', $data );
	}

	/**
	 * Callback function to render the section description in the settings page.
	 *
	 * This function outputs a brief description for the DragDrop uploader settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function section_callback(): void {
		printf(
			'<p>%s</p>',
			esc_html__( 'Configure the DragDrop uploader settings.', 'easy-file-uploader' )
		);
	}

	/**
	 * Callback function to render the "File Type Error Message" textarea in the settings page.
	 *
	 * This function retrieves the stored error message for invalid file types, sanitizes it,
	 * and outputs a textarea input field for user customization.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function file_type_error_message_callback(): void {
		// Retrieve the file type error message, defaulting to an empty string.
		$message = get_option( 'easy_dragdrop_file_type_error', '' );
		$message = sanitize_textarea_field( $message );

		// Output the textarea field with proper escaping.
		printf(
			'<textarea name="easy_dragdrop_file_type_error" rows="3" cols="50" maxlength="120">%s</textarea>',
			esc_textarea( $message )
		);

		// Output the description with proper escaping.
		printf(
			'<p class="help-text">%s</p>',
			esc_html__( 'Enter an error message to show when an uploaded file type is invalid. Leave blank to use the DragDrop uploader default message.', 'easy-file-uploader' )
		);
	}

	/**
	 * Callback function to render the "File Size Error Message" textarea in the settings page.
	 *
	 * This function retrieves the stored error message for files exceeding the size limit,
	 * sanitizes it, and outputs a textarea input field for user customization.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function file_size_error_message_callback(): void {
		// Retrieve the file size error message, defaulting to an empty string.
		$message = get_option( 'easy_dragdrop_file_size_error', '' );
		$message = sanitize_textarea_field( $message );

		// Output the textarea field with proper escaping.
		printf(
			'<textarea name="easy_dragdrop_file_size_error" rows="3" cols="50" maxlength="120">%s</textarea>',
			esc_textarea( $message )
		);

		// Output the description with proper escaping.
		printf(
			'<p class="help-text">%s</p>',
			esc_html__( 'Enter an error message to show when an uploaded file exceeds the file size limit.', 'easy-file-uploader' )
		);
	}

	/**
	 * Callback function to render the "Button Label" input field in the settings page.
	 *
	 * This function retrieves the stored button label, ensures its validity,
	 * and outputs a text input field for user customization.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function button_label_callback(): void {
		// Retrieve the button label option from the database, defaulting to an empty string.
		$button_label = get_option( 'easy_dragdrop_button_label', '' );
		$button_label = sanitize_text_field( $button_label );

		// Output the input field with proper escaping.
		printf(
			'<input type="text" name="easy_dragdrop_button_label" value="%s">',
			esc_attr( $button_label )
		);
	}

	/**
	 * Callback function to render the file types allowed input field in the settings page.
	 *
	 * This function retrieves the allowed file types from the database, sanitizes the value,
	 * and outputs an input field for users to modify it. It also includes a description
	 * to guide users on how to format the input.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function file_types_allowed_callback(): void {
		// Retrieve the allowed file types option from the database, defaulting to an empty string.
		$file_types = get_option( 'easy_dragdrop_file_types_allowed', '' );
		$file_types = is_string( $file_types ) ? sanitize_text_field( $file_types ) : '';

		// Output the input field with proper escaping to prevent XSS.
		printf(
			'<input type="text" name="easy_dragdrop_file_types_allowed" value="%s">',
			esc_attr( $file_types )
		);

		// Output the description with proper escaping for security.
		printf(
			'<p>%s</p>',
			esc_html__( 'Default allowed file types, separated by a comma (jpg, gif, pdf, etc). Can be overridden in the field settings.', 'easy-file-uploader' )
		);
	}

	/**
	 * Callback function to render the max file size setting field.
	 *
	 * This function retrieves the max file size option from the database and
	 * displays an input field along with a description. The value is sanitized
	 * and properly escaped for security.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function max_file_size_callback(): void {
		$max_file_size = get_option( 'easy_dragdrop_max_file_size' );
		$max_file_size = (int) $max_file_size; // Ensure it is strictly an integer.

		// Output a number input field with proper escaping and value handling.
		printf(
			'<input type="number" name="easy_dragdrop_max_file_size" value="%d" min="1" step="1">',
			esc_attr( $max_file_size )
		);

		// Display a help text for the input field.
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Default max. file size in MB. Can be overridden in the field settings.', 'easy-file-uploader' )
		);
	}
}
