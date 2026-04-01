<?php
/**
 * Main plugin controller class for ZIOR Drag Drop.
 *
 * This class bootstraps the plugin, loads dependencies, sets up internationalization,
 * and initializes core services including the plugin updater.
 *
 * @package ZIORWebDev\DragDrop
 * @since 1.0.0
 */

namespace ZIORWebDev\DragDrop;

/**
 * The core plugin class for ZIOR Drag Drop.
 *
 * Responsible for defining core constants, loading dependencies, setting up localization,
 * and initializing the plugin loader and updater.
 *
 * Implements the singleton pattern to ensure only one instance is used.
 *
 * @package ZIORWebDev\DragDrop
 * @since 1.0.0
 */
class Plugin {
	/**
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Current version of the plugin.
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Class constructor.
	 */
	public function __construct( string $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->version     = Helpers::get_plugin_version( $this->plugin_file );
	}

	/**
	 * Initialize the plugin.
	 *
	 * Sets up constants, includes required files, and initializes the plugin updater.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init(): void {
		$this->setup_constants();

		( new Settings() )->init();
		( new Assets() )->init();
		( new Integrations\Register() )->init();
		( new Routes() )->load();
		( new Hooks\Uploader() )->init();
	}

	/**
	 * Sets default plugin options.
	 *
	 * @since 1.1.7
	 * @return void
	 */
	private function set_default_options() {
		// Set default upload button label.
		update_option( 'easy_dragdrop_button_label', 'Browse Files' );

		// Set default file types.
		update_option( 'easy_dragdrop_file_types_allowed', 'jpg,jpeg,png,gif,bmp,webp,tiff,tif' );

		// Set the max file size. This is based on the server's upload_max_filesize.
		$default_max_file_size = Helpers::get_default_max_file_size();
		update_option( 'easy_dragdrop_max_file_size', $default_max_file_size );
	}

	/**
	 * Defines plugin constants used throughout the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants(): void {
		if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION' ) ) {
			define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_VERSION', $this->version );
		}

		if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_DIR' ) ) {
			define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_DIR', plugin_dir_path( $this->plugin_file ) );
		}

		if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_URL' ) ) {
			define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_URL', plugin_dir_url( $this->plugin_file ) );
		}

		if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_FILE' ) ) {
			define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_FILE', $this->plugin_file );
		}
	}

	/**
	 * Activate the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activate_plugin(): void {
		$this->set_default_options();

		// Let developers hook on plugin activate.
		do_action( 'easy_dragdrop_plugin_activate' );
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function deactivate_plugin(): void {
		// Let developers hook on plugin activate.
		do_action( 'easy_dragdrop_plugin_deactivate' );
	}
}
