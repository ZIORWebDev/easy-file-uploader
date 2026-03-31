<?php
/**
 * Assets Class
 *
 * Handles enqueuing scripts and styles for both frontend
 * and Elementor editor iframe.
 *
 * @package ZIORWebDev\DragDrop
 */

namespace ZIORWebDev\DragDrop;

use ZIORWebDev\DragDrop\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Assets
 */
class Assets {

	/**
	 * Constructor.
	 *
	 * Hooks asset loading into WordPress and Elementor.
	 */
	public function init() {
		/**
		 * Load scripts and styles in the frontend.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		/**
		 * Load scripts and styles in the Elementor editor.
		 */
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );

		/**
		 * Load scripts and styles in WordPress admin.
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );
	}

	/**
	 * Enqueue admin scripts and styles used by the uploader.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_style(
			'easy-dragdrop-uploader',
			ZIORWEBDEV_DRAGDROP_PLUGIN_URL . 'dist/admin/main.min.css',
			array(),
			ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'easy-dragdrop-uploader-editor',
			ZIORWEBDEV_DRAGDROP_PLUGIN_URL . 'dist/admin/main.min.js',
			array(),
			ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend scripts and styles used by the uploader.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$configurations = Helpers::get_uploader_configurations();

		// Vendor assets.
		wp_enqueue_style(
			'easy-dragdrop-vendors',
			ZIORWEBDEV_DRAGDROP_PLUGIN_URL . 'dist/vendors.min.css',
			array(),
			ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'easy-dragdrop-vendors',
			ZIORWEBDEV_DRAGDROP_PLUGIN_URL . 'dist/vendors.min.js',
			array(),
			ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION,
			true
		);

		/**
		 * Allow extensions to enqueue additional vendor assets.
		 *
		 * @param array $configurations Uploader configurations.
		 */
		do_action( 'enqueue_easy_dragdrop_scripts', $configurations );

		// Main uploader assets.
		wp_enqueue_style(
			'easy-dragdrop-uploader',
			ZIORWEBDEV_DRAGDROP_PLUGIN_URL . 'dist/main.min.css',
			array( 'easy-dragdrop-vendors' ),
			ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION
		);

		wp_enqueue_script(
			'easy-dragdrop-uploader',
			ZIORWEBDEV_DRAGDROP_PLUGIN_URL . 'dist/main.min.js',
			array( 'jquery', 'easy-dragdrop-vendors' ),
			ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION,
			true
		);

		wp_add_inline_script(
			'easy-dragdrop-uploader',
			'window.EasyDragDropUploader = ' . wp_json_encode( $configurations ) . ';',
			'before'
		);
	}
}
