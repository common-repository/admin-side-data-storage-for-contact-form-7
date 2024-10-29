<div class="tabs zestard-frm-setting-consent-tab">

        

    <h2><?php echo __( 'Forms Setting', ZT_DCFCF_TEXTDOMAIN ); ?></h2>



    <ul class="tab-links">

        <?php

        	global $wpdb;

            $cfdata          = apply_filters( 'zt_dcfcf_database', $wpdb );

            $table_name    = $cfdata->prefix.'cf_data_center_settings';

            $results   = $cfdata->get_results( "SELECT DISTINCT(zt_dcf_form_post_id) FROM $table_name ", OBJECT_K  );



            foreach ($results as $key => $value) 

            {

            	$title = get_the_title($key);

            	?>

                <li class=""><a href="#tab-<?php echo $key; ?>"> 

                    <?php echo __( $title, ZT_DCFCF_TEXTDOMAIN ); ?></a>

                </li>

            	<?php

            }

         ?>

    </ul>



    <?php

        global $wpdb;

        $data         = array();



        $cfdata       = apply_filters( 'zt_dcfcf_database', $wpdb );

        $table_name   = $cfdata->prefix.'cf_data_center_settings';

        $results   = $cfdata->get_results( "SELECT DISTINCT(zt_dcf_form_post_id) FROM $table_name ", OBJECT_K  );



        if(!empty($results))

        {

            $no = 1;

            foreach ($results as $key => $value) 

            {

                if($no == 1) $active = "active";

                else $active = "";

                $no++;



                $form_id = $key;

    ?>

                <div id="tab-<?php echo $form_id; ?>" class="ztdcfcf-tab <?php echo $active; ?>">

                    <div class="tab-content">

                        <div class="tab active">

                            <form method="POST" action="">

                                <div class="column-row" id="columnListId">

                                    <div class="instruction"><?php echo __( 'For Change the Column Order on Admin/Front View Just Drag and Drop Column.', ZT_DCFCF_TEXTDOMAIN ); ?></div>

                                    <?php

                                        $admin_results = $cfdata->get_results( "SELECT * FROM $table_name WHERE zt_dcf_form_post_id = '$form_id' AND zt_dcf_form_key = 'admin_show_hide'", OBJECT );

                                        $admin_first_row    = isset($admin_results[0]) ? unserialize( $admin_results[0]->zt_dcf_form_data ): 0 ;

                                        if(!empty($admin_first_row))

                                        {

                                    ?>

                                            <div class="zt-col-setting zt-admin-list">

                                                <div class="zt-column-title"> 

                                                    <span class='col-name'><b><?php echo __( 'Column Name', ZT_DCFCF_TEXTDOMAIN ); ?></b></span>

                                                    <span class='col-name'><b><?php echo __( 'Admin Show / Hide', ZT_DCFCF_TEXTDOMAIN ); ?></b></span>

                                                </div>

                                                <?php

                                                						                    

                				                    $admin_columns      = array();

                                                    $af = 0;

                                                    foreach ($admin_first_row as $admin_key => $admin_value) 

                                                    {

                                                        $af++;

                                                        ?>

                                                        <div class="zt-dcfcf-column admin-column" id="admin_<?php echo $key."_".$af; ?>">

                                                            <?php

                                                            	if($admin_value === 'true') $admin_checked = 'checked';

                                                            	else $admin_checked = '';

                                                                echo "<span class='col-name'>".esc_attr($admin_key)."</span>";

                                                            ?>                                                            

                                                            <span class="view" id="">

                                                                <input type="hidden" class="col-show" name="admin_show_hide[<?php echo esc_attr($admin_key); ?>]" value="false">

                                                                <input type="checkbox" class="col-show" name="admin_show_hide[<?php echo esc_attr($admin_key); ?>]" value="<?php echo esc_attr($admin_value); ?>" <?php echo esc_attr($admin_checked); ?>>

                                                            </span>

                                                            <br>

                                                        </div>

                                                        <?php

                                                    }

                                                ?>

                                            </div>

                                    <?php

                                        }

                                        

                                        $front_results = $cfdata->get_results( "SELECT * FROM $table_name WHERE zt_dcf_form_post_id = '$form_id' AND zt_dcf_form_key = 'front_show_hide'", OBJECT );

                                        $front_first_row    = isset($front_results[0]) ? unserialize( $front_results[0]->zt_dcf_form_data ): 0 ;

                                        if(!empty($front_first_row))

                                        {

                                            ?>

                                            <div class="zt-col-setting zt-column-list">

                                                <div class="zt-column-title"> 

                                                    <span class='col-name'><b><?php echo __( 'Column Name', ZT_DCFCF_TEXTDOMAIN ); ?></b></span>

                                                    <span class='col-name'><b><?php echo __( 'Front Show / Hide', ZT_DCFCF_TEXTDOMAIN ); ?></b></span>

                                                </div>

                                                <?php

                                                    

                				                    $front_columns      = array();

                                                    $ff = 0;

                                                    foreach ($front_first_row as $front_key => $front_value) 

                                                    {

                                                        $ff++;

                                                        ?>

                                                        <div class="zt-dcfcf-column front-column" id="front_<?php echo $key."_".$ff; ?>">

                                                            <?php

                                                        	if($front_value === 'true') $front_checked = 'checked';

                                                        	else $front_checked = '';

                                                            echo "<span class='col-name'>".esc_attr($front_key)."</span>";

                                                            ?>

                                                                <span class="view" id="">

                                                                    <input type="hidden" class="col-show" name="front_show_hide[<?php echo esc_attr($front_key); ?>]" value="false">

                                                                    <input type="checkbox" class="col-show" name="front_show_hide[<?php echo esc_attr($front_key); ?>]" value="<?php echo esc_attr($front_value); ?>" <?php echo esc_attr($front_checked); ?>>

                                                                </span>

                                                            <br>

                                                        </div>

                                                            <?php

                                                    }

                                                ?>   

                                            </div>

                                            <?php

                                        }

                                    ?>

                                    <div class="zt-col-setting zt-dcfcf-shortcode">

                                        <div class="zt-column-title"> 

                                            <span class='col-name'><b><?php echo __( 'Shortcode', ZT_DCFCF_TEXTDOMAIN ); ?></b></span>

                                        </div>

                                    </div>

                                    

                                </div><br>



                                <input type="text" name="zt-dcfcf-shortcode" size="25" class="shortcode-value" value="[cf7_data_center id='<?php echo esc_attr($form_id); ?>']" readonly />

                                <input type="button" name="copy-shortcode" class="button button-primary copy-shortcode" value="<?php echo __( 'Click To Copy Shortcode', ZT_DCFCF_TEXTDOMAIN ); ?>" />

                                <br><br>       

                                <input type="hidden" name="form-id" value="<?php echo esc_attr($form_id); ?>">

                                <input type="submit" name="zt-dcfcf-save-setting" value="<?php echo __( 'Save Changes', ZT_DCFCF_TEXTDOMAIN ); ?>" class="button button-primary" />

                            </form>

                        </div>

                    </div>

                </div>

    <?php   

            } 

        } 

        else 

        {

            ?><div class="no-setings"><b><?php echo __( 'No Any Contact Entry Found!', ZT_DCFCF_TEXTDOMAIN ); ?></b></div><?php

        }

    ?>

</div>



<?php

    if(isset($_POST['zt-dcfcf-save-setting']) && !empty($_POST['zt-dcfcf-save-setting']) )

    {

        $cfpid = $_POST['form-id'];

        $admin_settings = serialize($_POST['admin_show_hide']);

        $front_settings = serialize($_POST['front_show_hide']);



        global $wpdb;

        $cfdb       = apply_filters( 'zt_dcfcf_database', $wpdb );

        $table_name = $cfdb->prefix.'cf_data_center_settings';



        $cfdb->query("UPDATE $table_name SET zt_dcf_form_data = '$admin_settings' WHERE zt_dcf_form_post_id = $cfpid AND zt_dcf_form_key = 'admin_show_hide'");

        $cfdb->query("UPDATE $table_name SET zt_dcf_form_data = '$front_settings' WHERE zt_dcf_form_post_id = $cfpid AND zt_dcf_form_key = 'front_show_hide'");



		echo "<script type='text/javascript'>

        location.reload();

        </script>";		                        

    }

?>