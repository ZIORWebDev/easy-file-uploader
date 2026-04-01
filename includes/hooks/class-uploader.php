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

namespace ZIORWebDev\DragDrop\Hooks;

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
	 * Initialize the uploader by hooking into WordPress actions and filters.
	 */
	public function init() {
		add_filter( 'easy_dragdrop_process_field', array( $this, 'process_field' ), 10, 2 );
		add_filter( 'easy_dragdrop_validate_file_type', array( $this, 'validate_file_type' ), 10, 3 );
		add_filter( 'easy_dragdrop_validate_file_size', array( $this, 'validate_file_size' ), 10, 3 );
	}

	/**
	 * Processes the DragDrop field by moving files from the temporary directory to the upload directory.
	 *
	 * @since 1.0.0
	 * @param array $field The field data.
	 * @param mixed $record The form record instance.
	 * @return array The processed files.
	 */
	public function process_field( array $field, mixed $record = null ): array {
		$upload_dir  = wp_upload_dir();
		$upload_path = apply_filters( 'easy_dragdrop_upload_path', $upload_dir['path'] );
		$temp_path   = $upload_dir['basedir'] . '/easy-dragdrop-uploader-temp';
		$value_urls  = array();
		$value_paths = array();
		$files       = is_array( $field['raw_value'] ) ? $field['raw_value'] : array( $field['raw_value'] );

		if ( empty( $files ) ) {
			return array();
		}

		foreach ( $files as $unique_id ) {
			if ( empty( $unique_id ) ) {
				continue;
			}

			$source      = $temp_path . '/' . $unique_id;
			$destination = $upload_path . '/' . basename( $unique_id );

			// Move file to upload directory.
			$file_path = $this->move_file( $source, $destination );

			if ( $file_path ) {
				$value_paths[] = $file_path;
				$value_urls[]  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file_path );
			}

			// Delete temporary folder containing the file.
			$this->delete_files( dirname( $source ) );
		}

		$value_paths = implode( ', ', $value_paths );

		// Allow other developers to do something with the processed files.
		do_action( 'easy_dragdrop_process_files', $field, $value_paths, $value_urls, $record );

		return $value_urls;
	}

	/**
	 * Validate if the uploaded file size is within the allowed limit.
	 *
	 * @since 1.0.0
	 * @param bool  $valid    Whether the file is already considered valid.
	 * @param array $file     Uploaded file data from $_FILES.
	 * @param int   $max_size Maximum allowed file size in bytes.
	 * @return bool True if the file size is valid, false otherwise.
	 */
	public function validate_file_size( bool $valid, array $file, int $max_size ): bool {
		if ( $valid ) {
			return true;
		}

		return ( $file['size'] <= $max_size );
	}

	/**
	 * Validate if the uploaded file type is allowed.
	 *
	 * @since 1.0.0
	 * @param bool  $valid         Whether the file is already considered valid.
	 * @param array $file          Uploaded file data from $_FILES.
	 * @param array $allowed_types List of allowed MIME types (e.g., ['image/png', 'image/jpeg']).
	 * @return bool True if the file type is valid, false otherwise.
	 */
	public function validate_file_type( bool $valid, array $file, array $allowed_types ): bool {
		if ( $valid ) {
			return true;
		}

		// Get the MIME type using wp_check_filetype.
		$file_type = wp_check_filetype( $file['name'] );

		// Validate file type against allowed MIME types.
		return ( ! empty( $file_type['type'] ) && in_array( $file_type['type'], $allowed_types, true ) );
	}
}
