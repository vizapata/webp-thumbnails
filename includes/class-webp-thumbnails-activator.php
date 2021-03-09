<?php

/**
 * Fired during plugin activation
 *
 * @link       https://magnifik.co/autor/vizapata
 * @since      1.0.0
 *
 * @package    Webp_Thumbnails
 * @subpackage Webp_Thumbnails/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Webp_Thumbnails
 * @subpackage Webp_Thumbnails/includes
 * @author     Victor Zapata <vizapata@gmail.com>
 */
class Webp_Thumbnails_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		// set default options
		update_option("webp_thumbnails_quality", 75);
		update_option("webp_thumbnails_generate_for_original_file", true);
		update_option("webp_thumbnails_set_max_dimentions", true);
		update_option("webp_thumbnails_max_dimentions", array('width' => 1280, 'height' => 1280));
		update_option("webp_thumbnails_remove_original", false);
		update_option('webp_thumbnails_save_as_webp', true);
		update_option('webp_thumbnails_extension_management', 'REPLACE'); // replace, append
	}
}
