<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://magnifik.co/autor/vizapata
 * @since             1.0.0
 * @package           Webp_Thumbnails
 *
 * @wordpress-plugin
 * Plugin Name:       Webp Thumbnails
 * Plugin URI:        https://magnifik.co/webp-thumbnails
 * Description:       Wordpress plugin to generate Webp images on media upload
 * Version:           1.1.0
 * Author:            Victor Zapata
 * Author URI:        https://magnifik.co/autor/vizapata
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webp-thumbnails
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WEBP_THUMBNAILS_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-webp-thumbnails-activator.php
 */
function activate_webp_thumbnails() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webp-thumbnails-activator.php';
	Webp_Thumbnails_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webp-thumbnails-deactivator.php
 */
function deactivate_webp_thumbnails() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webp-thumbnails-deactivator.php';
	Webp_Thumbnails_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_webp_thumbnails' );
register_deactivation_hook( __FILE__, 'deactivate_webp_thumbnails' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-webp-thumbnails.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_webp_thumbnails() {

	$plugin = new Webp_Thumbnails();
	$plugin->run();

}
run_webp_thumbnails();
