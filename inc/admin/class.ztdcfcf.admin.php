<?php
/**
 * ZT_DCFCF_Admin Class
 *
 * Handles the Admin functionality.
 *
 * @package WordPress
 * @subpackage Admin side data storage for Contact Form 7 Admin
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZT_DCFCF_Admin' ) ) 
{
	/**
	 * The ZT_DCFCF_Admin Class
	 */
	class ZT_DCFCF_Admin 
	{
		var $action = null,
		    $filter = null;

		function __construct() 
		{
				   
		}

		
		
	}

	add_action( 'plugins_loaded', function() {
		ZT_DCFCF()->admin = new ZT_DCFCF_Admin;
	} );
}