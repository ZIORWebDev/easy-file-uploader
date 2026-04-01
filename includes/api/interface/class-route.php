<?php
/**
 * Route interface
 *
 * @package ZIORWebDev\DragDrop\Api\Interface
 * @since 1.0.0
 */
namespace ZIORWebDev\DragDrop\Api\Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route interface
 *
 * @package ZIORWebDev\DragDrop\Api\Interface
 * @since 1.0.0
 */
interface Route {

	/**
	 * Callback
	 *
	 * @param \WP_REST_Request $request The request.
	 * @return array The response.
	 */
	public function callback( \WP_REST_Request $request );

	/**
	 * Get name
	 *
	 * @return string The name.
	 */
	public function get_name();

	/**
	 * Get REST args
	 *
	 * @return array The REST args.
	 */
	public function get_rest_args();

	/**
	 * Get REST path
	 *
	 * @return string The REST path.
	 */
	public function get_rest_path();

	/**
	 * Get REST method
	 *
	 * @return string The REST method.
	 */
	public function get_rest_method();

	/**
	 * Get REST permission
	 *
	 * @return boolean The REST permission.
	 */
	public function get_rest_permission( \WP_REST_Request $request );

	/**
	 * Get REST URL
	 *
	 * @return string The REST URL.
	 */
	public function get_rest_url();
}
