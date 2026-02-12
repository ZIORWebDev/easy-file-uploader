<?php
/**
 * Plugin Name:  Easy DragDrop File Uploader
 * Plugin URI:   https://ziorweb.dev/plugin/easy-dragdrop-file-uploader
 * Description:  Enhances Elementor Pro Forms and Contact Form 7 with a drag and drop uploader for seamless file uploads.
 * Author:       ZIORWeb.Dev
 * Author URI:   https://ziorweb.dev
 * Version:      1.1.8
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:      GPL-2.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:  easy-file-uploader
 * Domain Path:  /languages
 * Tested up to: 6.9
 *
 * @package ZIORWebDev\DragDrop
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/gpl-2.0.txt>.
 */

use ZIORWebDev\DragDrop\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_DIR' ) ) {
	define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_URL' ) ) {
	define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'ZIORWEBDEV_DRAGDROP_PLUGIN_FILE' ) ) {
	define( 'ZIORWEBDEV_DRAGDROP_PLUGIN_FILE', __FILE__ );
}

require_once ZIORWEBDEV_DRAGDROP_PLUGIN_DIR . 'includes/functions.php';
require_once ZIORWEBDEV_DRAGDROP_PLUGIN_DIR . 'includes/classes/class-plugin.php';

$plugin_instance = Plugin::get_instance( __FILE__ );

register_activation_hook( __FILE__, array( $plugin_instance, 'activate_plugin' ) );
register_deactivation_hook( __FILE__, array( $plugin_instance, 'deactivate_plugin' ) );
