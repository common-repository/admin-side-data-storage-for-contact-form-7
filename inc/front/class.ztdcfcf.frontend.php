<?php
/**
 * ZT_DCFCF_Frontend Class
 *
 * Handles the Frontend functionality.
 *
 * @package WordPress
 * @subpackage Admin side data storage for Contact Form 7 Frontend
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZT_DCFCF_Frontend' ) ) {

	/**
	 * The ZT_DCFCF_Frontend Class
	 */
	class ZT_DCFCF_Frontend {

		var $action = null,
		    $filter = null;

		public $options;

		function __construct() {

		}
		
	}

	add_action( 'plugins_loaded', function() {
		ZT_DCFCF()->frontend = new ZT_DCFCF_Frontend;
	} );
}