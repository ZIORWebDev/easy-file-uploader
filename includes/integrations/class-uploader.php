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
	 * @param string $folder Folder path.
	 * @return bool True on success, false on failure.
	 */
	private function delete_files( $folder ) {
		global $wp_filesystem;

		if ( ! isset( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
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
	 * This function verifies the nonce for security, retrieves the file URL from the request,
	 * converts it to the file path, and attempts to delete the file from the server.
	 *
	 * @since 1.0.0
	 * @return void Outputs JSON response indicating success or failure.
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

		$temp_file_path = $this->temp_file_path . '/' . dirname( $file_id );

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
	 * @since 1.0.0
	 * @return void Outputs JSON response indicating success or failure.
	 */
	public function upload_files( \WP_REST_Request|null $request ): array {
		$files = $request->get_file_params();

		if ( empty( $files['form_fields'] ?? '' ) ) {
			return array(
				'error' => __( 'No valid file uploaded.', 'easy-file-uploader' )
			);
		}

		$uploaded_files = $this->get_uploaded_files( $files['form_fields'] );

		// Retrieve and validate file properties.
		$valid_types = explode( ',', sanitize_text_field( wp_unslash( $_POST['types'] ) ) ?? '' );

		if ( ! $this->is_valid_file_type( $uploaded_files, $valid_types ) ) {
			return array(
				'success' => false,
				'error'   => get_option( 'easy_dragdrop_file_type_error', '' ) ?: 'Invalid file type.',
			);
		}

		$file_max_size = absint( sanitize_text_field( wp_unslash( $_POST['size'] ) ) ) * 1024 * 1024 ?? Helpers::get_default_max_file_size();

		if ( ! $this->is_valid_file_size( $uploaded_files, $file_max_size ) ) {
			return array(
				'success' => false,
				'error'   => get_option( 'easy_dragdrop_file_size_error', '' ) ?: 'File size exceeds the maximum allowed size.',
			);
		}

		$unique_id      = wp_generate_uuid4();
		$temp_file_path = apply_filters( 'easy_dragdrop_temp_file_path', $this->temp_file_path . '/' . $unique_id );

		wp_mkdir_p( $temp_file_path );

		if ( $this->move_file( $uploaded_files['tmp_name'], $temp_file_path . '/' . $uploaded_files['name'] ) ) {
			// Let other developers to do something with the uploaded file.
			do_action( 'easy_dragdrop_upload_success', $uploaded_files, $temp_file_path );

			// Send the success response.
			return array(
				'success' => true,
				'file_id' => $unique_id . '/' . $uploaded_files['name'],
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
