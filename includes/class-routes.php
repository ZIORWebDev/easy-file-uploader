<?php
/**
 * Routes library
 *
 * @package ZIORWebDev\DragDrop
 * @since 1.0.0
 */
namespace ZIORWebDev\DragDrop;

use ZIORWebDev\DragDrop\Api\Endpoints;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes library
 *
 * @package ZIORWebDev\DragDrop\Api
 * @since 1.0.0
 */
class Routes {

	/**
	 * Routes
	 *
	 * @var array
	 */
	protected $routes = array();

	/**
	 * REST namespace
	 *
	 * @var string
	 */
	private static $rest_namespace = 'easy-file-uploader/v1';

	/**
	 * Load routes.
	 */
	public function load() {
		add_action( 'rest_api_init', array( $this, 'register_rest_api' ) );
	}

	/**
	 * Get REST namespace
	 *
	 * @return string The REST namespace.
	 */
	public static function get_namespace() {
		return self::$rest_namespace;
	}

	/**
	 * REST init
	 */
	public function register_rest_api() {
		new Endpoints\Upload();
		new Endpoints\Delete();
	}
}
