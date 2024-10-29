<?php
/**
 * ZT_DCFCF_Frontend_Action Class
 *
 * Zestard Admin side data storage for Contact Form 7 the Frontend functionality.
 *
 * @package WordPress
 * @subpackage Zestard Admin side data storage for Contact Form 7 Frontend
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'ZT_DCFCF_Frontend_Action' ) ) {

	/**
	 * The ZT_DCFCF_Frontend_Action Class
	 */
	class ZT_DCFCF_Frontend_Action {


		function __construct() {

        	add_action( 'wp_enqueue_scripts', array($this, 'zt_dcfcf_front_enqueue' ) );
			
			$this->zt_dcfcf_load_shortcode();
		}

		function zt_dcfcf_front_enqueue()
		{
			wp_enqueue_script('jquery');  

			wp_register_style( 'zt-dcfcf-front-style', ZT_DCFCF_URL.'inc/front/assets/css/zt-dcfcf-front-style.css', array(), time(), false );
			wp_enqueue_style( 'zt-dcfcf-front-style' );
		}

		function zt_dcfcf_load_shortcode()
		{
			add_shortcode( 'cf7_data_center', array( $this, 'zt_dcfcf_load_shortcode_view' ) );
		}
		
		function zt_dcfcf_load_shortcode_view($atts)
		{
        	global $wpdb;
		    $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
		    $zt_cfdc = $ztdb->prefix.'cf_data_center';
		    $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

  			$cf_post_id = ( isset($atts['id']) && !empty($atts['id']) ) ? (int) sanitize_text_field($atts['id']) : '';
  			$cf_post_orderby = ( isset($atts['orderby']) && !empty($atts['orderby']) ) ? sanitize_text_field($atts['orderby']) : 'zt_dcf_form_id';
  			$cf_post_order = ( isset($atts['order']) && !empty($atts['order']) ) ? sanitize_text_field($atts['order']) : 'DESC';
  			$cf_post_ppp = ( isset($atts['post_per_page']) && !empty($atts['post_per_page']) ) ? (int) sanitize_text_field($atts['post_per_page']) : '10';
            
            //$results       = $ztdb->get_results( "SELECT * FROM $zt_dcs WHERE zt_dcf_form_post_id = '$cf_post_id' AND zt_dcf_form_key = 'front_show_hide'", OBJECT );
            $results = $ztdb->get_results(
		    	$ztdb->prepare(
			        "SELECT * FROM $zt_dcs 
			        WHERE zt_dcf_form_post_id = %d 
			        AND zt_dcf_form_key = %s",
			        $cf_post_id, 'front_show_hide'
			    ),
			    OBJECT
			);


            $first_row            = isset($results[0]) ? unserialize( $results[0]->zt_dcf_form_data ): 0 ;
            $columns              = array();

            ob_start();
           
        	if( !empty($first_row) )
		    {	
		    	$show_col['srno'] = "Sr. No.";
		    	foreach ($first_row as $key => $value) 
		        {                
		            $key_val       = ucwords(str_replace( array('_', '-'), ' ', $key));
		            if($value == "true")
		                $show_col[$key] = $key_val;
		        }
		        $show_col['date'] = "Date";
		        
		    	foreach ($first_row as $key => $value) 
	            {
	                $data_array[$key] = esc_html($key);
	            }

	            //$num_rows = $ztdb->get_results( "SELECT * FROM $zt_cfdc WHERE zt_dcf_form_post_id = '$cf_post_id' and `zt_dcf_form_delete` = 0 ORDER BY $cf_post_orderby $cf_post_order", OBJECT );

	            $num_rows = $ztdb->get_results( 
				    $ztdb->prepare(
				        "SELECT * FROM $zt_cfdc 
				        WHERE zt_dcf_form_post_id = %d 
				        AND zt_dcf_form_delete = %d 
				        ORDER BY %s %s",
				        $cf_post_id, 0, $cf_post_orderby, $cf_post_order
				    ),
				    OBJECT 
				);


    			$total_posts = $wpdb->num_rows;
				
	            $posts_per_page = $cf_post_ppp;
				$start = 0;
				$paged = get_query_var( 'paged') ? get_query_var( 'paged', 1 ) : 1; // Current page number
				$start = ($paged-1)*$posts_per_page;

                $resultall = $ztdb->get_results( "SELECT * FROM $zt_cfdc WHERE zt_dcf_form_post_id = '$cf_post_id' and `zt_dcf_form_delete` = 0 ORDER BY $cf_post_orderby $cf_post_order LIMIT $start, $posts_per_page", OBJECT );

                /*$resultall = $ztdb->get_results(
				    $ztdb->prepare(
				        "SELECT * FROM $zt_cfdc 
				        WHERE zt_dcf_form_post_id = %d 
				        AND zt_dcf_form_delete = %d 
				        ORDER BY $cf_post_orderby %s 
				        LIMIT %d, %d",
				        $cf_post_id, 0, $cf_post_order, $start, $posts_per_page
				    ),
				    OBJECT
				);*/
                
                if( !empty($resultall) )
	            {
	            	$countSrNo = $start+1;
	                foreach ( $resultall as $result ) 
	                {
	                    $form_value = unserialize( $result->zt_dcf_form_data );	

	                    //starting array value display
	                    $preArr['srno'] = $countSrNo++;    
	                                    
	                    foreach ($form_value as $k => $value) 
	                    {
	                    	if ( is_array($value) ) 
		                    {
		                        $arr_str_data =  implode(', ',$value);
	                        	$data_array[$k] = esc_html( $arr_str_data );
	                        }
	                        else
	                        {
	                        	$data_array[$k] = $value;
	                        }
	                    }
	                    $data_array['date'] =  date("d-m-Y", strtotime($result->zt_dcf_form_date ));
	                    
	                    //marge value here
	                    $data_array = $preArr + $data_array;

	                    $data[] = $data_array;
	                }
	            }
				$tableIdValue = ZT_DCFCF_PREFIX.'_frm_'.$cf_post_id;
			?>
		       	<table class="zt-dcfcf-front-list" id="<?php echo $tableIdValue; ?>">
		       		<tr class="heading">
			       		<?php
			       		foreach ($show_col as $key => $value){
			            	echo "<td>".$value."</td>";
			            }
			       		?>
		       		</tr>
		       		
				       	<?php
						//data checked empty or not
						if(!empty($data)){
							foreach ($data as $array => $key){	
							?>
							<tr>
							<?php			            
								foreach ($key as $column => $value) 
								{
									if(array_key_exists($column, $show_col))
									{
										// wpcf7_ztdcfcf_uploads checked in value if exist then condition true for file type.
										if (preg_match('/wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/', $value)) {
										    
										    //ger href value form ancher tag
			                                $fileDatas = new SimpleXMLElement($value);
			                                
			                                //get file path
			                                $filePath = $fileDatas[0]->attributes()['href'];
			                                
			                                // Getting the original name
			                                $originalName = pathinfo(parse_url($filePath)['path'], PATHINFO_FILENAME);
			                                
			                                //get extension here
			                                $ext = pathinfo(
			                                    parse_url($filePath, PHP_URL_PATH), 
			                                    PATHINFO_EXTENSION
			                                );

			                                echo '<td><a href='.$filePath.' target="_blank">'.$originalName.'.'.$ext.'</a></td>';
										} else {
											echo "<td>".$value."</td>";
										}
									}
								}
							?>
							</tr>
							<?php
							}
						}else{
							//total col found and display empty message here
							$totalColumn = count($show_col);
							echo "<td colspan='".$totalColumn."' align='center'> Recode not Found.</td>";
						}
				       	?>
            	</table>

	            <div class="zt-dcfcf-pagination">
				    <?php
				    // Display Pagination
						$total_page = ceil( $total_posts / $posts_per_page); // Calculate Total pages
						$prev_arrow = is_rtl() ? '&rarr;' : '&larr;';
						$next_arrow = is_rtl() ? '&larr;' : '&rarr;';
						
						global $wp_query;
						$big = 999999999; // need an unlikely integer
						     if( !$current_page = get_query_var('paged') )
						         $current_page = 1;
						     if( get_option('permalink_structure') ) {
						         $format = 'page/%#%/';
						     } else {
						         $format = '&paged=%#%';
						     }
						    echo paginate_links(array(
						        'base'          => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						        'format'        => $format,
						        'current'       => max( 1, get_query_var('paged') ),
						        'total'         => $total_page,
						        'mid_size'      => 1,
						        'type'          => 'list',
						        'prev_text'     => $prev_arrow,
						        'next_text'     => $next_arrow,
						     ) );
				    ?>
				</div>
		<?php
        	}
	        ?>
            <?php
            return ob_get_clean();
		}

	}

	add_action( 'plugins_loaded', function() {
		ZT_DCFCF()->frontend->action = new ZT_DCFCF_Frontend_Action;
	} );
}