<?php
/**
 * Base class
 *
 * @package ZIORWebDev\DragDrop\Api\Endpoints
 * @since 1.0.0
 */
namespace ZIORWebDev\DragDrop\Api\Endpoints;

use ZIORWebDev\DragDrop\Api\Interface;
use ZIORWebDev\DragDrop\Routes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class
 *
 * @since 1.0.0
 * @package ZIORWebDev\DragDrop\Api\Endpoints
 */
abstract class Base implements Interface\Route {

	/**
	 * REST route path
	 *
	 * @var string
	 */
	protected $route_path = null;

	/**
	 * Check if the Nonce is valid
	 *
	 * @param \WP_REST_Request $request
	 * @return boolean
	 */
	protected function is_valid_nonce( \WP_REST_Request $request ): bool {
		$nonce = $request->get_header( 'x-wp-nonce' );

		return $nonce && wp_verify_nonce( $nonce, 'wp_rest' );
	}

	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		register_rest_route(
			Routes::get_namespace(),
			$this->get_rest_path(),
			array(
				'args'                => $this->get_rest_args(),
				'methods'             => $this->get_rest_method(),
				'callback'            => array( $this, 'callback' ),
				'permission_callback' => array( $this, 'get_rest_permission' ),
			)
		);
	}

	/**
	 * Get Rest path and method
	 *
	 * @return string
	 */
	public function get_name() {
		$path   = $this->get_rest_path();
		$method = strtolower( $this->get_rest_method() );

		return "$path/$method";
	}

	/**
	 * Get REST route path
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_rest_path() {
		return $this->route_path;
	}

	/**
	 * Get REST args
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_rest_args() {
		return array();
	}

	/**
	 * Get RESt URL
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_rest_url() {
		$blog_id   = get_current_blog_id();
		$namespace = Routes::get_namespace();
		$path      = $this->get_rest_path();

		return get_rest_url( $blog_id, "{$namespace}/$path" );
	}

	/**
	 * Get REST permission
	 *
	 * @since 1.0.0
	 * @return boolean|\WP_Error
	 */
	public function get_rest_permission( \WP_REST_Request $request ) {
		if ( $this->is_valid_nonce( $request ) ) {
			return true;
		}

		return new \WP_Error( 'rest_forbidden', __( 'Permission denied.', 'easy-file-uploader' ), array( 'status' => 403 ) );
	}
}
