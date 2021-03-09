<?php

/**
 * WordPress GD Image Editor
 *
 * @package WordPress
 * @subpackage Image_Editor
 */

/**
 * WordPress Image Editor Class for Image Manipulation through GD
 *
 * @since 3.5.0
 *
 * @see WP_Image_Editor_GD
 */
class WP_Image_Editor_WEBP extends WP_Image_Editor_GD
{

	/**
	 * Checks to see if editor supports the mime-type specified.
	 *
	 * @since 3.5.0
	 *
	 * @param string $mime_type
	 * @return bool
	 */
	public static function supports_mime_type($mime_type)
	{
		$support = parent::supports_mime_type($mime_type);
		if (
			!$support
			&& function_exists('imagecreatefromwebp')
			&& function_exists('imagewebp')
			&& get_option('webp_thumbnails_save_as_webp')
		) {
			$image_types = imagetypes();
			$support =  'image/webp' == $mime_type  && ($image_types & IMG_WEBP) != 0;
		}
		return $support;
	}

	/**
	 * @param resource $image
	 * @param string|null $filename
	 * @param string|null $mime_type
	 * @return WP_Error|array
	 */
	protected function _save($image, $filename = null, $mime_type = null)
	{
		list($filename, $extension, $mime_type) = $this->get_output_format($filename, $mime_type);

		$save_as_webp = get_option('webp_thumbnails_save_as_webp');

		if ($save_as_webp && $extension == 'webp') {

			if (!$filename) {
				$filename = $this->generate_filename(null, null, $extension);
			}

			$filenameWebp = $filename;
			/*
			$extension_management = get_option('webp_thumbnails_extension_management', 'REPLACE');
			
			if($mime_type != 'image/webp'){
				switch($extension_management){
					case 'REPLACE':
						$filenameWebp = str_replace(array('.jpg','.jpeg','.png','.gif'),'.'.$extension, $filename);
						break;
					case 'APPEND':
						$filenameWebp .= '.' .$extension;
						break;
					default:
					break;
				}
			}
			*/

			if (!$this->make_image($filenameWebp, 'imagewebp', array($image, $filenameWebp, $this->get_quality()))) {
				return parent::_save($image, $filename, $mime_type);
			} else {
				$mime_type = 'image/webp';
				$filename = $filenameWebp;
			}
		} else {
			// Use the parent option to save the file
			return parent::_save($image, $filename, $mime_type);
		}

		// Set correct file permissions
		$stat  = stat(dirname($filename));
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@chmod($filename, $perms);

		/**
		 * Filters the name of the saved image file.
		 *
		 * @since 2.6.0
		 *
		 * @param string $filename Name of the file.
		 */
		$return = array(
			'path'      => $filename,
			'file'      => wp_basename(apply_filters('image_make_intermediate_size', $filename)),
			'width'     => $this->size['width'],
			'height'    => $this->size['height'],
			'mime-type' => $mime_type,
		);
		return $return;
	}


	/**
	 * Returns stream of current image.
	 *
	 * @since 3.5.0
	 *
	 * @param string $mime_type The mime type of the image.
	 * @return bool True on success, false on failure.
	 */
	public function stream($mime_type = null)
	{
		/* 
		* TODO: Load config from options
		* Ej: if save as webp is not enabled, then return parent::stream();
		*/
		list($filename, $extension, $mime_type) = $this->get_output_format(null, $mime_type);
		$mime_type = 'image/webp';

		if ($mime_type != 'image/webp') return parent::stream($mime_type);
		else {
			header('Content-Type: image/webp');
			return imagewebp($this->image, null, $this->get_quality());
		}
	}


	/**
	 * Loads image from $this->file into new GD Resource.
	 *
	 * @since 3.5.0
	 *
	 * @return bool|WP_Error True if loaded successfully; WP_Error on failure.
	 */
	public function load()
	{
		if ($this->image) {
			return true;
		}

		if (!is_file($this->file) && !preg_match('|^https?://|', $this->file)) {
			return new WP_Error('error_loading_image', __('File doesn&#8217;t exist?'), $this->file);
		}

		// Set artificially high because GD uses uncompressed images in memory.
		wp_raise_memory_limit('image');

		$size = @getimagesize($this->file);
		if (!$size) {
			return new WP_Error('invalid_image', __('Could not read image size.'), $this->file);
		}

		if ($size['mime'] == 'image/webp' && function_exists('imagecreatefromwebp')) {
			$this->image = @imagecreatefromwebp($this->file);
		} else {
			// No es webp, cargar según criterios de la clase padre
			return parent::load();
		}

		if (!is_resource($this->image)) {
			return new WP_Error('invalid_image', __('File is not an image.'), $this->file);
		}

		if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
		}

		$this->update_size($size[0], $size[1]);
		$this->mime_type = $size['mime'];

		return $this->set_quality();
	}
}
