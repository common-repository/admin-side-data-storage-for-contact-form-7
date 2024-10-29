<?php
/**
 * Plugin Name: Admin side data storage for Contact Form 7
 * Description: Save and manage Contact Form 7 data. This add-on require Contact Form 7 plugin.
 * Version: 1.1.3
 * Author: Zestard Technologies
 * Author URI: https://profiles.wordpress.org/zestardtechnologies/
 * Developer: Zestard Technologies Pvt Ltd
 * Developer E-Mail: info@zestard.com
 * Text Domain: data-center-for-contact-form-7
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 *
 * @package Admin side data storage for Contact Form 7
 * @since 1.0
 */
if ( !defined( 'ZT_DCFCF_VERSION' ) ) {
	define( 'ZT_DCFCF_VERSION', '1.1.3' ); // Version of plugin
}

if ( !defined( 'ZT_DCFCF_FILE' ) ) {
	define( 'ZT_DCFCF_FILE', plugin_dir_url( __FILE__ ) ); // Plugin url
}

if ( !defined( 'ZT_DCFCF_DIR' ) ) {
	define( 'ZT_DCFCF_DIR', dirname( __FILE__ ) ); // Plugin dir
}

if ( !defined( 'ZT_DCFCF_BASENAME' ) ) {
	define( 'ZT_DCFCF_BASENAME', plugin_basename( __FILE__ ) ); // Plugin base name
}

if ( !defined( 'ZT_DCFCF_URL' ) ) {
	define( 'ZT_DCFCF_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}

if ( !defined( 'ZT_DCFCF_META_PREFIX' ) ) {
	define( 'ZT_DCFCF_META_PREFIX', 'ztdcfcf_' ); // Plugin metabox prefix
}

if ( !defined( 'ZT_DCFCF_PREFIX' ) ) {
	define( 'ZT_DCFCF_PREFIX', 'ztdcfcf' ); // Plugin prefix
}

if ( !defined( 'ZT_DCFCF_TEXTDOMAIN' ) ) {
	define( 'ZT_DCFCF_TEXTDOMAIN', 'zt-data-center-for-contact-form-7' ); // Plugin text-domain
}

ob_start();
/**
 * Initialize the main class
 */
if ( !function_exists( 'ZT_DCFCF' ) ) {

	//Initialize all the things.
	require_once( ZT_DCFCF_DIR . '/inc/class.' . ZT_DCFCF_PREFIX . '.php' );

	if ( is_admin() ) {
		require_once( ZT_DCFCF_DIR . '/inc/admin/class.' . ZT_DCFCF_PREFIX . '.admin.php' );
		require_once( ZT_DCFCF_DIR . '/inc/admin/class.' . ZT_DCFCF_PREFIX . '.admin.action.php' );
        require_once( ZT_DCFCF_DIR . '/inc/admin/class.wp.list.table.php' );
	}
	else{
		require_once( ZT_DCFCF_DIR . '/inc/front/class.' . ZT_DCFCF_PREFIX . '.frontend.php' );
		require_once( ZT_DCFCF_DIR . '/inc/front/class.' . ZT_DCFCF_PREFIX . '.frontend.action.php' );	
	}

}

//Global Define
$prefix  = 'zt_dcfcf_';

$plugin_file = plugin_basename(__FILE__);

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//if ( class_exists('WPCF7_ContactForm') ) {
if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
    
    register_activation_hook( __FILE__, 'zt_dcfcf_create_table' );

    /**
     * Plugin settings link
     */
    add_filter("plugin_action_links_$plugin_file", 'zt_dcfcf_settings_link' );

    //create new directory in upload directory.
    $upload_dir = wp_upload_dir(); 
    $directory_zt_dcfcf ='wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/';

    $uploadDirName = $upload_dir['basedir'].'/'.$directory_zt_dcfcf;
    
    if (!file_exists($uploadDirName)) {
        mkdir($uploadDirName, 0777, true);
    }

}
else{
    
    add_action('admin_notices', 'zt_dcfcf_adminNotice');
    
    function zt_dcfcf_adminNotice() {

            $zt_dcfcf_plugin = plugin_basename(__FILE__);      

            echo '<div class="error"><p>'; 
            echo esc_html__('Admin side data storage for Contact Form 7 requires active version of ', ZT_DCFCF_TEXTDOMAIN); 

            echo '<a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">'; 
            echo esc_html__('Contact Form 7', ZT_DCFCF_TEXTDOMAIN);
            echo "</a>";

            echo esc_html__(' plugin.', ZT_DCFCF_TEXTDOMAIN);
            echo "</p></div>";

           /**Do not allow this plugin to activate*/
            deactivate_plugins( $zt_dcfcf_plugin );

           /**Unset the $_GET variable which triggers the activation message*/
            unset($_GET['activate']);
    }
}

register_uninstall_hook( __FILE__, 'zt_dcfcf_delete_table' );

add_action( 'wpcf7_before_send_mail', 'zt_dcfcf_before_send_mail' );


// Create table
function zt_dcfcf_create_table(){

    global $wpdb;
    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
    $zt_cfdc = $ztdb->prefix.'cf_data_center';
    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

    if( $ztdb->get_var("SHOW TABLES LIKE '$zt_cfdc'") != $zt_cfdc ) 
    {
	    $charset_collate = $ztdb->get_charset_collate();

	    $sql = "CREATE TABLE $zt_cfdc (
	        zt_dcf_form_id bigint(20) NOT NULL AUTO_INCREMENT,
	        zt_dcf_form_post_id bigint(20) NOT NULL,
	        zt_dcf_form_data longtext NOT NULL,
            zt_dcf_form_read int(1) NOT NULL, 
            zt_dcf_form_bookmark int(1) NOT NULL, 
            zt_dcf_form_delete int(1) NOT NULL, 
	        zt_dcf_form_date date DEFAULT '0000-00-00' NOT NULL,
	        PRIMARY KEY  (zt_dcf_form_id)
	    ) $charset_collate;";

	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    dbDelta( $sql );
	}

    if( $ztdb->get_var("SHOW TABLES LIKE '$zt_dcs'") != $zt_dcs ) 
    {
        $charset_collate = $ztdb->get_charset_collate();

        $sql = "CREATE TABLE $zt_dcs (
            zt_dcf_form_id bigint(20) NOT NULL AUTO_INCREMENT,
            zt_dcf_form_post_id bigint(20) NOT NULL,
            zt_dcf_form_key varchar(20) NOT NULL, 
            zt_dcf_form_data longtext NOT NULL,
            PRIMARY KEY  (zt_dcf_form_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

// Delete table
function zt_dcfcf_delete_table() 
{
    global $wpdb;
    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
    $zt_cfdc = $ztdb->prefix.'cf_data_center';
    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';
    
    $del_zt_cfdc = "DROP TABLE IF EXISTS $zt_cfdc";
    $wpdb->query($del_zt_cfdc);

    $del_zt_dcs = "DROP TABLE IF EXISTS $zt_dcs";
    $wpdb->query($del_zt_dcs);
}

function zt_dcfcf_before_send_mail( $form_tag ) 
{
    global $wpdb;
    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
    $zt_cfdc = $ztdb->prefix.'cf_data_center';
    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

    $form = WPCF7_Submission::get_instance();

    if ( $form ) 
    {
        $cf_data = $form->get_posted_data();

        /* start upload file source code */

        $uploadedFiles = $form->uploaded_files();
        //upload file checked 
        if(!empty($uploadedFiles)){

            //if file exist then data store
            $arrFiles = array();

            foreach ($uploadedFiles as $key => $imageValueArr) {                
                //image url get
                $imageValue = $imageValueArr[0];
                
                //image value checked 
                if(!empty($imageValue)){

                    //directory name
                    $upload_dir = wp_upload_dir(); 
                    $uploadDirRootName = $upload_dir['basedir'];
                    $directory_zt_dcfcf ='wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/';
                    $newPath = $upload_dir['basedir'].'/'.$directory_zt_dcfcf;

                    $current_time = time();
                  
                    // Getting the original name
                    $originalName = pathinfo(parse_url($imageValue)['path'], PATHINFO_FILENAME);
                  
                    //get extension here
                    $ext = pathinfo(
                        parse_url($imageValue, PHP_URL_PATH), 
                        PATHINFO_EXTENSION
                    );
                                    
                    //new file name create 
                    $newFileName = $originalName.'_'.$current_time.'.'.$ext;
                    $fileURL  = $newPath.$newFileName;
                    
                    //copy file in upload directory
                    $fileCopied = copy($imageValue , $fileURL);

                    if (($fileCopied)) {
                        //push key and value here in array 
                        $fielURL = $upload_dir['baseurl'].'/'.$directory_zt_dcfcf.$newFileName;

                        //$htmlContent = "<a href='".$fielURL."' target='_blank'>".$originalName.'.'.$ext."</a>";
                        $htmlContent = "<a href='".$fielURL."' target='_blank'>".$fielURL."</a>";
                        $arrFiles[$key] = $htmlContent;
                        //echo "</br>successfully image</br>";
                    }
                } //closed image value checked 
                else{
                    //if not upload file then empty value set
                    $arrFiles[$key] = "";
                }
            } //closed foreach loop

        } //closed upload file checked 
        
        //if file field exist then new path set in those field
        if(!empty($arrFiles)){
            foreach ($arrFiles as $key => $value) {
                $cf_data[$key] = $value;
            }
        }
      
        /* end upload file source code */   
        
    	/* ztdb before save data. */
        $cf_data = apply_filters('zt_dcfcf_before_save_data', $cf_data);

        do_action( 'zt_dcfcf_before_save', $cf_data );

        $cf_post_id = $form_tag->id();
        $cf_value   = serialize( $cf_data );
        $cf_date    = current_time('Y-m-d H:i:s');

        $ztdb->insert( $zt_cfdc, array(
            'zt_dcf_form_post_id' => $cf_post_id,
            'zt_dcf_form_data'   => $cf_value,
            'zt_dcf_form_read'  => 0,
            'zt_dcf_form_bookmark'=> 0,
            'zt_dcf_form_delete' => 0,
            'zt_dcf_form_date'    => $cf_date
        ) );        

        /* ztdb after save data */
        $insert_id = $ztdb->insert_id;
        do_action( 'zt_dcfcf_after_save_data', $insert_id );

        $results = $ztdb->get_results( "SELECT zt_dcf_form_post_id FROM $zt_dcs WHERE zt_dcf_form_post_id = '$cf_post_id'", OBJECT );

        if(empty($results))
        {
            foreach ($cf_data as $key => $value) {
                $col[$key] = 'true';
            }

            $cf_settings = serialize($col);

            $ztdb->insert( $zt_dcs, array(
                'zt_dcf_form_post_id' => $cf_post_id,
                'zt_dcf_form_key'  => 'admin_show_hide',
                'zt_dcf_form_data'   => $cf_settings
            ) );

            $ztdb->insert( $zt_dcs, array(
                'zt_dcf_form_post_id' => $cf_post_id,
                'zt_dcf_form_key'  => 'front_show_hide',
                'zt_dcf_form_data'   => $cf_settings
            ) );
        }
    }

}

function zt_dcfcf_settings_link( $links ) {
    $forms_link = '<a href="admin.php?page=zt-dcfcf-settings">Settings</a>';
    array_unshift($links, $forms_link);
    return $links;
}