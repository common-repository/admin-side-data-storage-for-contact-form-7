<?php
/**
 * ZT_DCFCF_Admin_Action Class
 *
 * Handles the admin functionality.
 *
 * @package WordPress
 * @subpackage Admin side data storage for Contact Form 7 Admin Actions
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZT_DCFCF_Admin_Action' ) ){

	/**
	 *  The ZT_DCFCF_Admin_Action Class
	 */
	class ZT_DCFCF_Admin_Action {

		private $zt_dcf_form_post_id, $search, $zt_dcf_form_id;

		function __construct()  {

			add_action( 'init', array($this, 'zt_dcfcf_init') );
        	add_action( 'admin_menu', array($this, 'zt_dcfcf_admin_table_list_page' ) );

        	//action to add function with ajax 
			add_action( 'wp_ajax_change_status', array($this, 'zt_dcfcf_change_status') );
			add_action( 'wp_ajax_nopriv_change_status', array($this, 'zt_dcfcf_change_status') );
			add_action( 'wp_ajax_change_bookmark', array($this, 'zt_dcfcf_change_bookmark') );
			add_action( 'wp_ajax_nopriv_change_bookmark', array($this, 'zt_dcfcf_change_bookmark') );
		}

		public function zt_dcfcf_init()
		{	        
	    	wp_register_style( 'zt_dcfcf_admin_css', ZT_DCFCF_URL.'inc/admin/assets/css/zt-dcfcf-admin-style.css', array(), time(), false);
	        wp_enqueue_style( 'zt_dcfcf_admin_css' );

	        wp_register_script( 'zt_dcfcf_jquery', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js', array(), ZT_DCFCF_VERSION, true);
	        wp_enqueue_script( 'zt_dcfcf_jquery' );

	        wp_register_script( 'zt_dcfcf_admin_js', ZT_DCFCF_URL.'inc/admin/assets/js/zt-dcfcf-admin-script.js', array(), ZT_DCFCF_VERSION, true);
	        wp_enqueue_script( 'zt_dcfcf_admin_js' );

	        wp_localize_script( 'zt_dcfcf_admin_js', 'ztcfd_admin_ajax_object', 
	            array('ajax_url' => admin_url( 'admin-ajax.php' ) )
	            );
		}

		/**
	     * Menu item will allow us to load the page to display the table
	     */
	    public function zt_dcfcf_admin_table_list_page()
	    {
	        add_menu_page( __( 'ASDS CF7', ZT_DCFCF_TEXTDOMAIN ), __( 'ASDS CF7', ZT_DCFCF_TEXTDOMAIN ), 'manage_options' , 'zt-dcfcf-list', array($this, 'zt_dcfcf_table_list_page'), 'dashicons-excerpt-view' );

	        add_submenu_page('zt-dcfcf-list', 'Settings', 'Settings', 'manage_options' , 'zt-dcfcf-settings',  array($this, 'zt_dcfcf_settings') );
	    }

	    function zt_dcfcf_settings()
	    {
	        require_once 'inc/settings.php';
	    }

		/**
	     * Display the list table page
	     *
	     */
	    public function zt_dcfcf_table_list_page()
	    {
	        $fid  = empty($_GET['fid']) ? 0 : (int) $_GET['fid'];
	        $ufid = empty($_GET['ufid']) ? 0 : (int) $_GET['ufid'];
	        $trash = empty($_GET['status']) ? '' : $_GET['status'];

	        if ( !empty($fid) && empty($_GET['ufid']) ) 
	        {	
				$this->zt_dcf_form_post_id = (int) $_GET['fid'];

	        	$ListTable = new ZT_DCFCF_Main_List_Table();

		        $ListTable->prepare_items();
		        ?>
		        <?php $ListTable->views(); ?>
		            <div class="wrap">
		                <div id="icon-users" class="icon32"></div>
		                <?php if(!empty($trash) && ($trash == 'trash') ) { ?>
		                	<h2><?php _e( 'Trash List : ', ZT_DCFCF_TEXTDOMAIN ); ?><?php _e(esc_html(ucwords(get_the_title( $this->zt_dcf_form_post_id )))); ?></h2><hr><br>
		            	<?php } else { ?>
		                	<h2><?php _e(esc_html(ucwords(get_the_title( $this->zt_dcf_form_post_id )))); ?></h2><hr><br>
		            	<?php } ?>
		                	<?php $ListTable->get_views(); ?>
		                <form method="get" action="">
		                	<?php $ListTable->extra_tablenavs('top'); ?>
		                </form>
		                <form method="post" action="">
		                    <?php $ListTable->display(); ?>
		                </form>
		            </div>
		        <?php
	        }
	        else if( !empty($ufid) && !empty($fid) )
	        {
	        	$this->zt_dcf_form_post_id = esc_sql( $fid ) ;
		       	$this->zt_dcf_form_id = esc_sql( $ufid );
		       	$this->form_details_page();
	        }
	        else
	        {
	        	$ListTable = new ZT_DCFCF_Main_List_Table();
		        $ListTable->prepare_items();
		        ?>
		            <div class="wrap">
		                <div id="icon-users" class="icon32"></div>
		                <h2><?php _e( 'Contact Forms List', ZT_DCFCF_TEXTDOMAIN ); ?></h2>
		                <?php $ListTable->display(); ?>
		            </div>
		        <?php
	        }
	    }

		public function form_details_page()
		{
        	global $wpdb;
		    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
		    $zt_cfdc = $ztdb->prefix.'cf_data_center';
		    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

	        if ( is_numeric($this->zt_dcf_form_post_id) && is_numeric($this->zt_dcf_form_id) ) 
	        {
	        	$results    = $ztdb->get_results( "SELECT * FROM $zt_cfdc WHERE zt_dcf_form_post_id = $this->zt_dcf_form_post_id AND zt_dcf_form_id = $this->zt_dcf_form_id LIMIT 1", OBJECT );
	        }

	        if ( empty($results) ) 
	        {
	            wp_die( $message = 'Not valid contact form' );
	        }
	        ?>
		        <div class="wrap">
		            <div id="welcome-panel" class="welcome-panel">
		                <div class="welcome-panel-content">
		                    <div class="welcome-panel-column-container zt_dcfcf_admin_list">
		                    	<div class="alignleft actions">
		                    		<?php $backURL = admin_url('admin.php?page=zt-dcfcf-list&fid='.$this->zt_dcf_form_post_id); ?>
			                    	<a href="<?php echo $backURL; ?>" class="button">Back</a>
			                    </div>

		                        <?php do_action('ztcfd_before_formdetails_title',$this->zt_dcf_form_post_id ); ?>
		                        <h3><?php _e(esc_html(get_the_title( $this->zt_dcf_form_post_id ))); ?></h3>
		                        <?php do_action('ztcfd_after_formdetails_title', $this->zt_dcf_form_post_id ); ?>
		                        
		                        <table border="1" width="100%" align="center">
			                        <?php $form_data  = unserialize( $results[0]->zt_dcf_form_data );
			                        
			                        $countData = count($form_data);
									$countValue = 0;
			                        foreach ($form_data as $key => $data):

		                                if ( is_array($data) ) 
		                                {
		                                    $key      = ucwords( $key );
		                                    $arr_str_data =  implode(', ',$data);
		                                    $arr_str_data =  esc_html( $arr_str_data );
		                                    echo '<tr><th>'.$key.'</th><td style="text-align:center;"> '. nl2br($arr_str_data) .'</td></tr>';
		                                }
		                                else
		                                {
		                                    $key = ucwords( $key );
		                                    
		                                    // wpcf7_ztdcfcf_uploads checked in value if exist then condition true for file type.
											if (preg_match('/wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/', $data)) {
											    
											    //ger href value form ancher tag
				                                $fileDatas = new SimpleXMLElement($data);
				                                
				                                //get file path
				                                $filePath = $fileDatas[0]->attributes()['href'];
				                                
				                                // Getting the original name
				                                $originalName = pathinfo(parse_url($filePath)['path'], PATHINFO_FILENAME);
				                                
				                                //get extension here
				                                $ext = pathinfo(
				                                    parse_url($filePath, PHP_URL_PATH), 
				                                    PATHINFO_EXTENSION
				                                );
				                                echo '<tr><th>'.$key.'</th><td style="text-align:center;"> <a href='.$filePath.' target="_blank">'.$originalName.'.'.$ext.'</a></td></tr>';
											} else {
												echo '<tr><th>'.$key.'</th><td style="text-align:center;"> '.nl2br($data).'</td></tr>';
											}
		                                }

		                                //date display in last row
		                                if(++$countValue === $countData) {
									    	echo '<tr><th>Date</th><td style="text-align:center;"> '. $results[0]->zt_dcf_form_date .'</td></tr>';
									  	}
			                            
			                        endforeach;
			                        $zt_dcf_form_id = $results[0]->zt_dcf_form_id;

			                        $ztdb->query( "UPDATE $zt_cfdc SET zt_dcf_form_read = 1 WHERE zt_dcf_form_id = $zt_dcf_form_id" );
			                        ?>
		                        </table><br>
		                    </div>
		                </div>
		            </div>
		        </div>
	        <?php
	        do_action('ztcfd_after_formdetails', $this->zt_dcf_form_post_id );
	    }

	    function zt_dcfcf_change_status()
	    {
			ob_clean();
			global $wpdb;
		    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
		    $zt_cfdc = $ztdb->prefix.'cf_data_center';
		    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';
			
			if(isset($_POST['id']) && isset($_POST['status']))
			{
			    $cf_id = (int) esc_sql($_POST['id']);
			    $cf_status = esc_sql($_POST['status']);

			    $zt_dcf_form_read = ($cf_status == 1) ? 0 : 1 ;
			    $response = $ztdb->query(
			        "UPDATE $zt_cfdc SET zt_dcf_form_read = '$zt_dcf_form_read' WHERE zt_dcf_form_id = '$cf_id'"
			    );
			    echo $response;
			    wp_die();
			}
	    }

	    function zt_dcfcf_change_bookmark()
	    {
			ob_clean();
			global $wpdb;
		    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
		    $zt_cfdc = $ztdb->prefix.'cf_data_center';
		    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';
			
		    if(isset($_POST['id']) && isset($_POST['status']))
			{
				$cf_id = (int) esc_sql($_POST['id']);
			    $cf_status = esc_sql($_POST['status']);
			    
			    $zt_dcf_form_bookmark = ($cf_status == 1) ? 0 : 1 ;
			    $response = $ztdb->query(
			        "UPDATE $zt_cfdc SET zt_dcf_form_bookmark = '$zt_dcf_form_bookmark' WHERE zt_dcf_form_id = '$cf_id'"
			    );

			    echo $response;
			    wp_die();
			}
	    }
	}

	add_action( 'plugins_loaded' , function() {
		ZT_DCFCF()->admin->action = new ZT_DCFCF_Admin_Action;
	} );
}