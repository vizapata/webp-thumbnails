<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://magnifik.co/autor/vizapata
 * @since      1.0.0
 *
 * @package    Webp_Thumbnails
 * @subpackage Webp_Thumbnails/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Webp_Thumbnails
 * @subpackage Webp_Thumbnails/admin
 * @author     Victor Zapata <vizapata@gmail.com>
 */
class Webp_Thumbnails_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
	}

	public function file_is_displayable_image($result, $path)
	{
		if (!$result && defined('IMAGETYPE_WEBP')) {
			$info = @getimagesize($path);
			$result = $info[2] == IMAGETYPE_WEBP;
		}
		return $result;
	}

	private function log($data)
	{
		error_log(print_r($data, true));
	}

	public function wp_generate_attachment_metadata($metadata, $attachment_id)
	{
		return $metadata;
	}

	public function wp_image_editors($editors = array())
	{
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'class-wp-image-editor-webp.php';
		array_unshift($editors, 'WP_Image_Editor_WEBP');
		return $editors;
	}

	public function wp_editor_set_quality($default_quality, $mime_type)
	{
		return get_option("webp_thumbnails_quality", $default_quality);
	}

	public function wp_handle_upload($upload, $context)
	{
		// Load settings from wordpress options
		$generate_webp_for_original_file = get_option("webp_thumbnails_generate_for_original_file");
		$save_as_webp = get_option('webp_thumbnails_save_as_webp');
		$set_max_dimentions = get_option("webp_thumbnails_set_max_dimentions");
		$max_dimentions = get_option("webp_thumbnails_max_dimentions");

		// Load the available editor
		$editor = wp_get_image_editor($upload['file'], array('mime_type' => $upload['type']));
		if (is_wp_error($editor)) return $upload;

		// If the set_max_dimentions option has been set -> then resize
		if ($set_max_dimentions && isset($max_dimentions['width']) && isset($max_dimentions['height'])) {
			if (is_wp_error($editor->resize($max_dimentions['width'], $max_dimentions['height'], false))) {
				return $upload;
			}
		} // ./if

		$filename  = $upload['file'];
		$mime_type = $upload['type'];
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$original_file_path = $filename;

		// if the save_as_webp is established for the original file
		if ($save_as_webp && $generate_webp_for_original_file && $editor->supports_mime_type('image/webp')) {

			$extension = 'webp';
			$mime_type = 'image/webp';

			$filename = sprintf(
				'%s%s%s.%s',
				pathinfo($original_file_path, PATHINFO_DIRNAME),
				DIRECTORY_SEPARATOR,
				pathinfo($original_file_path, PATHINFO_FILENAME),
				$extension
			);
		} // ./if
		$this->log($upload);
		error_log("saving file as mimetype {$mime_type} and filename {$filename} ");
		$data = $editor->save($filename, $mime_type);

		if (!is_wp_error($data) && $data) {
			$upload['file'] = $data['path'];
			$upload['type'] = $mime_type;
			$upload['url']  = sprintf(
				'%s%s%s.%s',
				pathinfo($upload['url'], PATHINFO_DIRNAME),
				'/',
				pathinfo($upload['file'], PATHINFO_FILENAME),
				$extension
			);

			$remove_original = get_option("webp_thumbnails_remove_original");
			if ($remove_original)	unlink($original_file_path);
		}
		$this->log($upload);

		return $upload;
	}

	public function mime_types($mime_types)
	{
		if (is_array($mime_types) && !in_array('image/webp', $mime_types)) {
			$mime_types['webp'] = 'image/webp';
		}
		return $mime_types;
	}

	public function admin_menu()
	{
		add_submenu_page(
			'upload.php',
			'Webp Thumbnails',
			'Webp Thumbnails',
			'manage_options',
			'webp_thumbnails',
			array($this, 'options_page')
		);
	}

	public function options_page()
	{
		require_once plugin_dir_path(__FILE__)  . 'partials/webp-thumbnails-settings.php';
	}


	public function admin_init()
	{
		register_setting('webpThumbnailsConfigPage', 'webp_thumbnails_save_as_webp');
		register_setting('webpThumbnailsConfigPage', 'webp_thumbnails_set_max_dimentions');
		register_setting('webpThumbnailsConfigPage', 'webp_thumbnails_max_dimentions');

		add_settings_section(
			'webp_thumbnails_general_settings',
			__('General settings', 'webp-thumbnails'),
			array($this, 'general_settings_section_callback'),
			'webpThumbnailsConfigPage'
		);

		add_settings_field(
			'webp_thumbnails_save_as_webp',
			__('Enable saving as WebP', 'webp-thumbnails'),
			array($this, 'save_as_webp'),
			'webpThumbnailsConfigPage',
			'webp_thumbnails_general_settings'
		);


		add_settings_field(
			'webp_thumbnails_set_max_dimentions',
			__('Set maximum dimentions', 'webp-thumbnails'),
			array($this, 'set_max_dimentions'),
			'webpThumbnailsConfigPage',
			'webp_thumbnails_general_settings'
		);

		add_settings_field(
			'webp_thumbnails_max_dimentions',
			__('Maximum dimentions', 'webp-thumbnails'),
			array($this, 'max_dimentions'),
			'webpThumbnailsConfigPage',
			'webp_thumbnails_general_settings'
		);
	}

	function set_max_dimentions()
	{
		$set_max_dimentions = get_option('webp_thumbnails_set_max_dimentions');
?>
		<input type='checkbox' name='webp_thumbnails_set_max_dimentions' <?php checked($set_max_dimentions, 1); ?> value='1'>
	<?php
	}

	function max_dimentions()
	{
		$max_dimentions = get_option("webp_thumbnails_max_dimentions");
	?>
		<input type='number' name='webp_thumbnails_max_dimentions[width]' value='<?php echo $max_dimentions['width']; ?>'> Ancho x
		<input type='number' name='webp_thumbnails_max_dimentions[height]' value='<?php echo $max_dimentions['height']; ?>'> Alto
	<?php
	}


	function save_as_webp()
	{
		$save_as_webp = get_option('webp_thumbnails_save_as_webp');
	?>
		<input type='checkbox' name='webp_thumbnails_save_as_webp' <?php checked($save_as_webp, 1); ?> value='1'>
<?php

	}


	function general_settings_section_callback()
	{
		echo __('Enable redimension', 'webp-thumbnails');
	}
}
