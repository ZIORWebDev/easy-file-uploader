<?php
/**
 * Uploader class for the DragDrop File Uploader plugin.
 *
 * This class integrates the FilePond uploader with forms,
 * providing a seamless drag-and-drop upload experience in WordPress.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */

namespace ZIORWebDev\DragDrop\Integrations;

use ZIORWebDev\DragDrop\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles drag-and-drop file uploads within forms.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */
class Uploader {
	/**
	 * Path to the temporary file directory.
	 *
	 * @var string
	 */
	private ?string $temp_file_path = '';

	/**
	 * Deletes all files inside a folder.
	 *
	 * Security: This method only deletes files within the plugin's designated
	 * temporary upload directory to prevent accidental or malicious deletion
	 * of files outside the intended scope.
	 *
	 * @param string $folder Folder path.
	 * @return bool True on success, false on failure.
	 */
	private function delete_files( $folder ) {
		global $wp_filesystem;

		if ( ! isset( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		// SECURITY: Ensure the folder exists before attempting deletion
		if ( ! file_exists( $folder ) ) {
			return false;
		}

		// SECURITY: Validate that the folder is within the temp directory
		if ( ! Helpers::validate_path_is_within_directory( str_replace( $this->temp_file_path . '/', '', $folder ), $this->temp_file_path ) ) {
			// Prevent deletion outside temp directory
			return false;
		}

		return $wp_filesystem->rmdir( $folder, true );
	}

	/**
	 * Retrieves the uploaded file from the Elementor form fields.
	 *
	 * This function extracts the uploaded file from the `$_FILES` superglobal.
	 * Since it deals with file uploads, sanitation is not applied here.
	 *
	 * @since 1.0.0
	 * @param array $files The uploaded files from the Elementor form.
	 * @return array|bool The uploaded file array or false if no file is uploaded.
	 */
	private function get_uploaded_files( array $files ): array|bool {
		if ( empty( $files ) || ! is_array( $files ) ) {
			return false;
		}

		$field_name = sanitize_text_field( array_key_first( $files['name'] ) );
		$file_keys  = array( 'name', 'type', 'tmp_name', 'error', 'size' );
		$file       = array();

		// Extract the file from the multidimensional $_FILES structure.
		foreach ( $file_keys as $key ) {
			$sanitize_callback = in_array( $key, array( 'name', 'type', 'tmp_name' ), true ) ? 'sanitize_text_field' : 'intval';
			$file[ $key ]      = is_array( $files[ $key ][ $field_name ] )
				? $sanitize_callback( $files[ $key ][ $field_name ][0] )
				: $sanitize_callback( $files[ $key ][ $field_name ] );
		}

		return $file;
	}

	/**
	 * Validate the file type against allowed types.
	 *
	 * This function applies the 'easy_dragdrop_validate_file_type' filter to allow
	 * external modification of the validation logic.
	 *
	 * @since 1.0.0
	 * @param array $file        File data array containing file details.
	 * @param array $valid_types Array of allowed file types.
	 * @return bool True if the file type is valid, false otherwise.
	 */
	private function is_valid_file_type( array $file, array $valid_types ): bool {
		return apply_filters( 'easy_dragdrop_validate_file_type', false, $file, $valid_types );
	}

	/**
	 * Validate the file size against the maximum allowed size.
	 *
	 * This function applies the 'easy_dragdrop_validate_file_size' filter to allow
	 * external modification of the validation logic.
	 *
	 * @since 1.0.0
	 * @param array $file    File data array containing file details.
	 * @param int   $max_size Maximum allowed file size in bytes.
	 * @return bool True if the file size is valid, false otherwise.
	 */
	private function is_valid_file_size( array $file, int $max_size ): bool {
		return apply_filters( 'easy_dragdrop_validate_file_size', false, $file, $max_size );
	}

	/**
	 * Safely move a file to avoid overwriting an existing file.
	 *
	 * @since 1.0.0
	 * @param string $source      The source file path.
	 * @param string $destination The destination file path.
	 * @return string|false The new file path if successful, false on failure.
	 */
	private function move_file( $source, $destination ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$path      = pathinfo( $destination );
		$dir       = $path['dirname'];
		$filename  = $path['filename'];
		$extension = isset( $path['extension'] ) ? '.' . $path['extension'] : '';

		$counter         = 1;
		$new_destination = $destination;

		while ( $wp_filesystem->exists( $new_destination ) ) {
			$new_destination = sprintf( '%s/%s-%d%s', $dir, $filename, $counter, $extension );
			++$counter;
		}

		if ( $wp_filesystem->move( $source, $new_destination ) ) {
			// Set the file to be publicly readable.
			$wp_filesystem->chmod( $new_destination, 0644 );

			return $new_destination;
		}

		return false;
	}

	/**
	 * Constructor.
	 *
	 * Hooks into the uploader.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set the temporary file path.
		$this->temp_file_path = wp_upload_dir()['basedir'] . '/easy-dragdrop-uploader-temp';
	}

	/**
	 * Handles the removal of an uploaded file.
	 *
	 * Security: This method performs strict path validation to prevent directory traversal attacks.
	 * Only files within the plugin's temporary directory can be deleted.
	 *
	 * @since 1.0.0
	 * @return array Array containing success status and message.
	 */
	public function remove_files( \WP_REST_Request|null $request ): array {
		// Retrieve the file id from the request body.
		$file_id = sanitize_text_field( $request->get_body() );

		if ( ! $file_id ) {
			return array(
				'success' => false,
				'error'   => __( 'Missing file ID.', 'easy-file-uploader' )
			);
		}

		// SECURITY: Sanitize the file identifier to prevent path traversal
		$sanitized_id = Helpers::sanitize_file_identifier( $file_id );
		if ( ! $sanitized_id ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid file ID format.', 'easy-file-uploader' )
			);
		}

		// Build the path to the directory containing the file
		$temp_file_path = $this->temp_file_path . '/' . dirname( $sanitized_id );

		// SECURITY: Validate that the directory path is within the temp directory
		if ( ! Helpers::validate_path_is_within_directory( dirname( $sanitized_id ), $this->temp_file_path ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid file path.', 'easy-file-uploader' )
			);
		}

		// SECURITY: Verify the directory actually exists before attempting deletion
		if ( ! file_exists( $temp_file_path ) ) {
			return array(
				'success' => false,
				'error'   => __( 'File not found.', 'easy-file-uploader' )
			);
		}

		// Delete the directory and its contents
		if ( $this->delete_files( $temp_file_path ) ) {
			return array(
				'success' => true,
				'message' => __( 'Files deleted successfully.', 'easy-file-uploader' )
			);
		} else {
			return array(
				'success' => false,
				'error'   => __( 'Failed to delete files.', 'easy-file-uploader' )
			);
		}
	}

	/**
	 * Handles file uploads.
	 *
	 * This function verifies security checks, validates the uploaded file,
	 * processes the file upload, and saves it to a custom directory.
	 *
	 * Security: All file paths are validated to ensure they remain within the
	 * plugin's designated temporary upload directory.
	 *
	 * @since 1.0.0
	 * @return array Array containing upload result with success status and file information.
	 */
	public function upload_files( \WP_REST_Request|null $request ): array {
		$files = $request->get_file_params();

		if ( empty( $files['form_fields'] ?? '' ) ) {
			return array(
				'error' => __( 'No valid file uploaded.', 'easy-file-uploader' )
			);
		}

		$uploaded_files = $this->get_uploaded_files( $files['form_fields'] );

		// Retrieve and validate the allowed MIME types or file extensions.
		$raw_types = $request->get_param( 'types' );
		if ( ! is_string( $raw_types ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid file types.', 'easy-file-uploader' ),
			);
		}

		$raw_types   = sanitize_text_field( $raw_types );
		$valid_types = array_filter( array_map( 'trim', explode( ',', $raw_types ) ) );
		foreach ( $valid_types as $valid_type ) {
			if ( ! preg_match( '/^[a-z0-9][a-z0-9.+_-]*(?:\/(?:[a-z0-9][a-z0-9.+_-]*|\*))?$/i', $valid_type ) ) {
				return array(
					'success' => false,
					'error'   => __( 'Invalid file types.', 'easy-file-uploader' ),
				);
			}
		}
		$valid_types = array_values( $valid_types );

		if ( ! $this->is_valid_file_type( $uploaded_files, $valid_types ) ) {
			return array(
				'success' => false,
				'error'   => get_option( 'easy_dragdrop_file_type_error', '' ) ?: 'Invalid file type.',
			);
		}

		$raw_size = $request->get_param( 'size' );
		if ( null === $raw_size || '' === $raw_size ) {
			$raw_size = Helpers::get_default_max_file_size();
		}

		if ( ! is_int( $raw_size ) && ! is_string( $raw_size ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid file size limit.', 'easy-file-uploader' ),
			);
		}

		$file_size_mb = filter_var(
			preg_replace( '/[^0-9]/', '', (string) $raw_size ),
			FILTER_VALIDATE_INT,
			array(
				'options' => array( 'min_range' => 1 ),
			)
		);

		if ( false === $file_size_mb ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid file size limit.', 'easy-file-uploader' ),
			);
		}

		$file_size_mb = min( $file_size_mb, Helpers::get_default_max_file_size() );
		$file_max_size = $file_size_mb * 1024 * 1024;

		if ( ! $this->is_valid_file_size( $uploaded_files, $file_max_size ) ) {
			return array(
				'success' => false,
				'error'   => get_option( 'easy_dragdrop_file_size_error', '' ) ?: 'File size exceeds the maximum allowed size.',
			);
		}

		// Generate a unique ID for this upload session
		$unique_id = wp_generate_uuid4();

		// Ensure the filter-selected path remains within the temporary upload directory.
		$temp_file_path = apply_filters( 'easy_dragdrop_temp_file_path', trailingslashit( $this->temp_file_path ) . $unique_id );
		if ( ! is_string( $temp_file_path ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid temporary directory path.', 'easy-file-uploader' ),
			);
		}

		$temp_base_path   = trailingslashit( wp_normalize_path( $this->temp_file_path ) );
		$temp_file_path   = wp_normalize_path( $temp_file_path );
		$path_prefix_match = '\\' === DIRECTORY_SEPARATOR
			? 0 === strncasecmp( $temp_file_path, $temp_base_path, strlen( $temp_base_path ) )
			: str_starts_with( $temp_file_path, $temp_base_path );

		if ( ! $path_prefix_match ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid temporary directory path.', 'easy-file-uploader' ),
			);
		}

		$relative_temp_path = substr( $temp_file_path, strlen( $temp_base_path ) );
		if ( ! Helpers::validate_path_is_within_directory( $relative_temp_path, $this->temp_file_path, true ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid temporary directory path.', 'easy-file-uploader' ),
			);
		}

		// Create the temporary directory for this upload
		if ( ! wp_mkdir_p( $temp_file_path ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Failed to create temporary directory.', 'easy-file-uploader' ),
			);
		}

		if ( ! Helpers::validate_path_is_within_directory( $relative_temp_path, $this->temp_file_path ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid temporary directory path.', 'easy-file-uploader' ),
			);
		}

		// Sanitize the uploaded filename to prevent directory traversal
		$safe_filename = basename( $uploaded_files['name'] );
		$safe_filename = sanitize_file_name( $safe_filename );

		if ( empty( $safe_filename ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Invalid file name.', 'easy-file-uploader' ),
			);
		}

		if ( $this->move_file( $uploaded_files['tmp_name'], $temp_file_path . '/' . $safe_filename ) ) {
			// Let other developers to do something with the uploaded file.
			do_action( 'easy_dragdrop_upload_success', $uploaded_files, $temp_file_path );

			// Send the success response.
			return array(
				'success' => true,
				'file_id' => $unique_id . '/' . $safe_filename,
			);
		} else {
			// Let other developers to do something with the error.
			do_action( 'easy_dragdrop_upload_failure', $uploaded_files, $temp_file_path );

			// Send the error response.
			return array(
				'success' => false,
				'error'   => 'Failed to move uploaded file.',
			);
		}
	}
}
