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
class Webp_Thumbnails_Admin {

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
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Webp_Thumbnails_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Webp_Thumbnails_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	// 	wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/webp-thumbnails-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Webp_Thumbnails_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Webp_Thumbnails_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

	//	wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/webp-thumbnails-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function file_is_displayable_image( $result, $path ) {
		if(!$result && defined( 'IMAGETYPE_WEBP' ) ) {
			$info = @getimagesize( $path );
			$result = $info[2] == IMAGETYPE_WEBP;
		}
		return $result;
	}

	private function log($data){
		error_log(print_r($data, true));
	}

	public function wp_generate_attachment_metadata( $metadata, $attachment_id) {
		return $metadata;
	}
	
	public function wp_image_editors( $editors=array()) {
		require_once __DIR__ . DIRECTORY_SEPARATOR .'class-wp-image-editor-webp.php';
		array_unshift($editors, 'WP_Image_Editor_WEBP');
		return $editors;
	}

	public function wp_editor_set_quality($default_quality, $mime_type){
		return get_option("webp_thumbnails_quality", $default_quality);
	}

	public function wp_handle_upload($upload, $context){
		// Load settings from wordpress options
		$generate_webp_for_original_file = get_option("webp_thumbnails_generate_for_original_file");
		$save_as_webp = get_option('webp_thumbnails_save_as_webp');
		$set_max_dimentions = get_option("webp_thumbnails_set_max_dimentions");
		$max_dimentions = get_option("webp_thumbnails_max_dimentions");
		
		// Load the available editor
		$editor = wp_get_image_editor( $upload['file'] , array('mime_type'=>$upload['type']));
		if(is_wp_error( $editor ) ) return $upload;

		// If the set_max_dimentions option has been set -> then resize
		if ( $set_max_dimentions && isset($max_dimentions['width']) && isset($max_dimentions['height']) ) {
			if ( is_wp_error( $editor->resize( $max_dimentions['width'], $max_dimentions['height'], false ) ) ) {
				return $upload;
			}
		} // ./if

		$filename  = $upload['file'];
		$mime_type = $upload['type'];
		$extension = pathinfo($filename, PATHINFO_EXTENSION );
		$original_file_path = $filename;

		// if the save_as_webp is established for the original file
		if($save_as_webp && $generate_webp_for_original_file && $editor->supports_mime_type( 'image/webp' )){

			$extension = 'webp';
			$mime_type = 'image/webp';

			$filename = sprintf('%s%s%s.%s',
				pathinfo($original_file_path, PATHINFO_DIRNAME) ,
				DIRECTORY_SEPARATOR,
				pathinfo($original_file_path, PATHINFO_FILENAME),
				$extension
			);

		} // ./if
		$this->log($upload);
		error_log("saving file as mimetype {$mime_type} and filename {$filename} ");
		$data = $editor->save($filename, $mime_type);

		if ( ! is_wp_error( $data ) && $data ) {
			$upload['file'] = $data['path'];
			$upload['type'] = $mime_type;
			$upload['url']  = sprintf('%s%s%s.%s',
				pathinfo($upload['url'], PATHINFO_DIRNAME) ,
				'/',
				pathinfo($upload['file'], PATHINFO_FILENAME),
				$extension
			);

			$remove_original = get_option("webp_thumbnails_remove_original");
			if($remove_original)	unlink($original_file_path);
		}
		$this->log($upload);

		return $upload;
	}

	public function mime_types($mime_types){
		if(is_array($mime_types) && !in_array('image/webp',$mime_types)){
			$mime_types['webp']='image/webp';
		}
		return $mime_types;
	}


}
