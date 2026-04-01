<?php
/**
 * Helpers class
 *
 * @package ZIORWebDev\DragDrop
 * @since 1.0.0
 */
namespace ZIORWebDev\DragDrop;
use Mimey\MimeTypes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! function_exists( 'get_file_data' ) ) {
	require_once ABSPATH . 'wp-includes/functions.php';
}

/**
 * Helpers class
 *
 * @package ZIORWebDev\DragDrop
 * @since 1.0.0
 */
class Helpers {

	/**
	 * Array reduce
	 *
	 * @param array    $array
	 * @param callable $callback
	 * @param mixed    $carry
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function array_reduce(
		array $array,
		callable $callback,
		$carry = null,
	) {
		foreach ( $array as $key => $value ) {
			$carry = $callback( $carry, $key, $value, $array );
		}

		return $carry;
	}

	/**
	 * Retrieves the current plugin version from the plugin file header.
	 *
	 * Uses WordPress core's get_file_data() function to read the version
	 * from the plugin file's metadata.
	 *
	 * @param string $plugin_file Absolute path to the plugin file.
	 * @return string             Plugin version.
	 * @since 1.0.0
	 */
	public static function get_plugin_version( string $plugin_file ): string {
		$plugin_data = get_file_data( $plugin_file, array( 'Version' => 'Version' ) );

		return $plugin_data['Version'] ?? '';
	}

	/**
	 * Retrieves the current plugin name from the plugin file header.
	 *
	 * Uses WordPress core's get_file_data() function to read the version
	 * from the plugin file's metadata.
	 *
	 * @param string $plugin_file Absolute path to the plugin file.
	 * @return string             Plugin name.
	 * @since 1.0.0
	 */
	public static function get_plugin_name( string $plugin_file ): string {
		$plugin_data = get_file_data( $plugin_file, array( 'Name' => 'Name' ) );

		return $plugin_data['Name'] ?? '';
	}

	/**
	 * Retrieves the list of allowed HTML attributes for input elements.
	 *
	 * This function defines a whitelist of safe attributes for input elements,
	 * ensuring security by preventing unwanted HTML injection.
	 *
	 * @return array Allowed HTML attributes for input elements.
	 * @since 1.0.0
	 */
	public static function get_allowed_html(): array {
		$allowed_html = array(
			'input' => array(
				'type'           => array(),
				'name'           => array(),
				'value'          => array(),
				'placeholder'    => array(),
				'class'          => array(),
				'id'             => array(),
				'checked'        => array(),
				'readonly'       => array(),
				'disabled'       => array(),
				'required'       => array(),
				'data-filesize'  => array(),
				'data-filetypes' => array(),
				'data-label'     => array(),
				'data-maxfiles'  => array(),
				'multiple'       => array(),
			),
		);

		return $allowed_html;
	}

	/**
	 * Retrieves the default maximum file size in MB.
	 *
	 * This function calculates the maximum file size in MB based on the WordPress
	 * upload limit.
	 *
	 * @since 1.0.0
	 * @return int The default maximum file size in MB.
	 */
	public static function get_default_max_file_size(): int {
		$max_size = wp_max_upload_size();

		// Convert the max size to MB.
		$max_size = (int) $max_size / 1024 / 1024;

		return $max_size;
	}

	/**
	 * Retrieves all plugin options.
	 *
	 * This function fetches the list of option names and retrieves their values
	 * from the WordPress options table.
	 *
	 * @since 1.0.0
	 * @return array An associative array of option names and their corresponding values.
	 */
	public static function get_plugin_options(): array {
		$plugin_options = array();
		$options        = self::get_options();

		foreach ( $options as $option ) {
			$plugin_options[ $option['option_name'] ] = get_option( $option['option_name'] ) ?? '';
		}

		return $plugin_options;
	}

	/**
	 * Retrieves the DragDrop uploader configuration settings.
	 *
	 * This function fetches stored options related to file handling and
	 * applies the 'easy_dragdrop_uploader_configurations' filter for customization.
	 *
	 * @since 1.0.0
	 * @return array An associative array of configuration settings.
	 */
	public static function get_uploader_configurations(): array {
		$plugin_options      = self::get_plugin_options();
		$accepted_file_types = $plugin_options['easy_dragdrop_file_types_allowed'];
		$accepted_file_types = self::convert_extentions_to_mime_types( $accepted_file_types );

		$uploader_configurations = array(
			'acceptedFileTypes' => $accepted_file_types,
			'labelIdle'         => $plugin_options['easy_dragdrop_button_label'] ?? 'Browse Image',
			'labelMaxFileSize'  => apply_filters( 'easy_dragdrop_label_max_file_size', '' ),
			'rest'              => rest_url( Routes::get_namespace() ),
			'nonce'             => wp_create_nonce( 'wp_rest' )
		);

		$file_type_error = $plugin_options['easy_dragdrop_file_type_error'] ?? '';

		if ( ! empty( $file_type_error ) ) {
			$uploader_configurations['labelFileTypeNotAllowed'] = $file_type_error;
		}

		$file_size_error = $plugin_options['easy_dragdrop_file_size_error'] ?? '';

		if ( ! empty( $file_size_error ) ) {
			$uploader_configurations['labelMaxFileSizeExceeded'] = $file_size_error;
		}

		return apply_filters( 'easy_dragdrop_uploader_configurations', $uploader_configurations, $plugin_options );
	}

	/**
	 * Converts a comma-separated list of file extensions into an array of MIME types.
	 *
	 * This function takes a string of file extensions, splits them into an array,
	 * and converts them to their corresponding MIME types using the MimeTypes class.
	 * Developers can modify the list of extensions and the MimeTypes instance
	 * via filters.
	 *
	 * @since 1.0.0
	 * @param string $extentions Comma-separated list of file extensions.
	 * @return array List of corresponding MIME types.
	 */
	public static function convert_extentions_to_mime_types( string $extentions ): array {
		$mime_types = array();
		$extensions = array_map( 'trim', explode( ',', $extentions ) );
		$mimes      = new MimeTypes();

		/**
		 * Filters the MimeTypes instance used for retrieving MIME types.
		 *
		 * @since 1.0.0
		 * @param MimeTypes $mimes The MimeTypes instance.
		 */
		$mimes = apply_filters( 'easy_dragdrop_mimes_instance', $mimes );

		/**
		 * Filters the list of file extensions before converting to MIME types.
		 *
		 * @since 1.0.0
		 * @param array $extensions The list of file extensions.
		 */
		$extensions = apply_filters( 'easy_dragdrop_file_extensions', $extensions );

		foreach ( $extensions as $extension ) {
			$mime_type = $mimes->getMimeType( $extension );

			if ( empty( $mime_type ) ) {
				continue;
			}

			$mime_types[] = $mime_type;
		}

		return $mime_types;
	}

	/**
	 * Returns the settings options for the plugin.
	 *
	 * This function defines the settings options for the plugin.
	 *
	 * @since 1.0.0
	 * @return array The settings options.
	 */
	public static function get_options(): array {
		$options = array(
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_button_label',
				'type'         => 'string',
			),
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_file_types_allowed',
				'type'         => 'string',
			),
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_enable_preview',
				'type'         => 'integer',
			),
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_preview_height',
				'type'         => 'integer',
			),
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_file_type_error',
				'type'         => 'string',
			),
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_file_size_error',
				'type'         => 'string',
			),
			array(
				'option_group' => 'easy_dragdrop_options_group',
				'option_name'  => 'easy_dragdrop_max_file_size',
				'type'         => 'integer',
			),
		);

		return apply_filters( 'easy_dragdrop_options', $options );
	}
}
