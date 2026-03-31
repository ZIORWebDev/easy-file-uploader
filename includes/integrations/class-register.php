<?php
/**
 * Register Class
 *
 * This file contains the definition of the Register class, which is responsible
 * for registering the Easy DragDrop Uploader plugin with Elementor forms.
 *
 * @package    ZIORWebDev\DragDrop
 * @since      1.0.0
 */

namespace ZIORWebDev\DragDrop\Integrations;

use ZIORWebDev\DragDrop\Integrations\Fields;
use ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the registration of DragDrop uploader form fields.
 *
 * @since 1.0.0
 */
class Register {

	/**
	 * Initialize class.
	 *
	 * Hooks into Elementor Pro to register custom form fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		/**
		 * Register Elementor form pro dragdrop uploader field
		 */
		add_action( 'elementor_pro/forms/fields/register', array( $this, 'register_elementor_form_fields' ), 10 );

		/**
		 * Register CF7 form pro dragdrop uploader field
		 */
		$cf7_uploader = Fields\CF7Uploader::get_instance();
		add_action( 'wpcf7_init', array( $cf7_uploader, 'register' ), 10 );

		// Let other developers to register their own uploader.
		do_action( 'zior_dragdrop_register_uploader' );
	}

	/**
	 * Registers the DragDrop uploader field with Elementor's form field registry.
	 *
	 * @since 1.0.0
	 * @param ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar $form Elementor form fields object.
	 * @return void
	 */
	public function register_elementor_form_fields( Form_Fields_Registrar $form ): void {
		$form->register( new Fields\ElementorUploader() );
	}
}
