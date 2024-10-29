<?php
/**
 * ZT_DCFCF Class
 *
 * Handles the plugin functionality.
 *
 * @package WordPress
 * @subpackage Admin side data storage for Contact Form 7
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZT_DCFCF' ) ) {

	/**
	 * The main ZT_DCFCF class
	 */
	class ZT_DCFCF {

		private static $_instance = null;

		var $admin = null,
		    $front = null;

		public static function instance() {

			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}

		function __construct() {

		}

	}
}

function ZT_DCFCF() {
	return ZT_DCFCF::instance();
}

ZT_DCFCF();