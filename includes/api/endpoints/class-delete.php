<?php
/**
 * Upload endpoint
 *
 * @package ZIORWebDev\DragDrop\Api\Endpoints
 * @since 1.0.0
 */
namespace ZIORWebDev\DragDrop\Api\Endpoints;

use ZIORWebDev\DragDrop\Integrations\Uploader;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Options Value endpoint
 *
 * @package ZIORWebDev\DragDrop\Api\Endpoints
 * @since 1.0.0
 */
class Delete extends Base {

	/**
	 * Route path
	 *
	 * @var string
	 */
	protected $route_path = 'delete';

	/**
	 * Callback
	 *
	 * @param \WP_REST_Request $request The request.
	 * @return array The response.
	 */
	public function callback( \WP_REST_Request $request ) {
		$uploader = new Uploader();
		$removed  = $uploader->remove_files( $request );

		// Failure
		if ( isset( $removed['success'] ) && $removed['success'] === false ) {
			return new \WP_REST_Response(
				$removed,
				400
			);
		}

		// Success
		return new \WP_REST_Response(
			$removed ?? array(),
			200
		);
	}

	/**
	 * Get REST args
	 *
	 * @return array The REST args.
	 */
	public function get_rest_args() {
		return array();
	}

	/**
	 * Get REST method
	 *
	 * @return string The REST method.
	 */
	public function get_rest_method() {
		return \WP_REST_Server::DELETABLE;
	}
}
