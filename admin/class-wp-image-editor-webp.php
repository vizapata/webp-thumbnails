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
class WP_Image_Editor_WEBP extends WP_Image_Editor_GD {
	
	/**
	 * @param resource $image
	 * @param string|null $filename
	 * @param string|null $mime_type
	 * @return WP_Error|array
	 */
	protected function _save( $image, $filename = null, $mime_type = null ) {
		list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, $mime_type );

		/* TODO: Load config from options
		* Ej: if save as webp is not enabled, then return parent::_save();
    * Ej: add/replace extension option
		*/
    $estension = "webp"; // jpg.webp, png.webp, gif.webp
    $mime_type = "image/webp";

		if ( ! $filename ) {
      $filename = $this->generate_filename( null, null, $extension );
    }
		$filename = str_replace(".jpg",".webp",$filename);
		
		if ( 'image/webp' != $mime_type ) return parent::_save($image, $filename, $mime_type);
		else{
			if ( ! $this->make_image( $filename, 'imagewebp', array( $image, $filename, $this->get_quality() ) ) ) {
				return new WP_Error( 'image_save_error', __( 'Image Editor Save Failed' ) );
			}
		}

		// Set correct file permissions
		$stat  = stat( dirname( $filename ) );
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@ chmod( $filename, $perms );

		/**
		 * Filters the name of the saved image file.
		 *
		 * @since 2.6.0
		 *
		 * @param string $filename Name of the file.
		 */
		$return = array(
			'path'      => $filename,
			'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
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
	public function stream( $mime_type = null ) {
		/* 
		* TODO: Load config from options
		* Ej: if save as webp is not enabled, then return parent::stream();
		*/
		list( $filename, $extension, $mime_type ) = $this->get_output_format( null, $mime_type );
		$mime_type = 'image/webp';
		
		if($mime_type != 'image/webp') return parent::stream($mime_type);
		else {
			header( 'Content-Type: image/webp' );
      return imagewebp( $this->image, null, $this->get_quality() );
		}
	}

}
