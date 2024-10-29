<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class ZT_DCFCF_Main_List_Table extends WP_List_Table
{
    private $zt_dcf_form_post_id, $search, $column_titles;

    /**
     * Constructor start subpage
     */
    public function __construct()
    {

        parent::__construct(
            array(
                'singular' => 'zt_dcf_form',
                'plural'   => 'zt_dcf_forms',
                'ajax'     => false
            )
        );
    }

    /**
     * Outputs extra controls to be displayed between bulk actions and pagination.
     *
     */
    function extra_tablenavs( $which ) 
    {
        $fid  = empty($_GET['fid']) ? 0 : (int) $_GET['fid'];
        $ufid = empty($_GET['ufid']) ? 0 : (int) $_GET['ufid'];

        if ( !empty($fid) && empty($_GET['ufid']) ) 
        {
            ?><div class="alignleft actions"><?php
                
                if ( 'top' === $which && !is_singular() ) {
                    
                    ob_start();
                    
                    echo "<div class='data_filter'>";

                        $this->dates_dropdown();
                    
                        do_action( 'restrict_manage_productions', $this->screen->post_type, $which );
         
                        $output = ob_get_clean();
         
                        echo "<div>";

                            if ( ! empty( $output ) ) {
                                echo $output;
                                    submit_button( __( 'Filter', ZT_DCFCF_TEXTDOMAIN ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
                            }

                            $all_url = remove_query_arg(array('from_dcfcf','to_dcfcf','s','filter_action','orderby','order'));
                            echo "<a href='{$all_url }' class='button'>Reset</a><br><br>";

                        echo "</div>";

                    echo "</div>";

                    if( isset($_GET['status']) && ($_GET['status'] == 'trash') )
                    {
                        $query_args = array( 'page' => $_GET['page'], 'fid' => $fid );
                        $all_link = add_query_arg( $query_args, admin_url( '/admin.php' ) );
                        echo "<a href='{$all_link }' class='button'>All</a><br><br>";
                    }
                    else
                    {
                        $query_args = array( 'page' => $_GET['page'], 'fid' => $fid, 'status' => 'trash' );
                        $trash_link = add_query_arg( $query_args, admin_url( '/admin.php' ) );
                        echo "<a href='{$trash_link }' class='button'>Trash</a><br><br>";
                    }
                }
                     
            ?></div><br><br><?php
            do_action( 'manage_productions_extra_tablenav', $which );
        }       
    }

    /**
     * Displays a dates drop-down for filtering on the Events list table.
     *
     */
    function dates_dropdown( ) {
        ?>
            <div><input type="hidden" name="page" value="zt-dcfcf-list"></div>
            <div><input type="hidden" name="fid" value="<?php echo esc_attr( $_GET['fid'] ); ?>"></div>
            <div><input type="hidden" name="status" value="<?php echo (isset( $_GET['status'] ) && !empty( $_GET['status'] )) ? esc_attr( $_GET['status'] ) : "" ; ?>"></div>

            <div><b><label><?php _e('From Date : ', ZT_DCFCF_TEXTDOMAIN); ?></label></b>
                <input type="date" id="zt_from_date" name="from_dcfcf" max="<?php echo esc_attr(date('Y-m-d')); ?>" value="<?php echo !empty($_GET['from_dcfcf']) ? esc_attr($_GET['from_dcfcf']) : ''; ?>" required/></div>

            <div><b><label><?php _e('To Date : ', ZT_DCFCF_TEXTDOMAIN); ?></label></b>
                <input type="date" id="zt_to_date" name="to_dcfcf" max="<?php echo esc_attr(date('Y-m-d')); ?>" min="" value="<?php echo !empty($_GET['to_dcfcf']) ? esc_attr($_GET['to_dcfcf']) : ''; ?>" required/></div>

            <div><input type="search" id="search-search-input" name="s" value="<?php echo !empty($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search Data', ZT_DCFCF_TEXTDOMAIN); ?>"></div>
        <?php
    }


    /**
     * Prepare the items for the table to process
     */
    public function prepare_items()
    {
        $_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

        global $wpdb;
        $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
        $zt_cfdc = $ztdb->prefix.'cf_data_center';
        $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

        if( isset($_GET['fid']) && !empty($_GET['fid']) && !isset($_GET['ufid']) )
        {
            $this->zt_dcf_form_post_id = (int) $_GET['fid'];
            $search = empty( $_REQUEST['s'] ) ? false :  esc_sql( $_GET['s'] );
            echo $this->search;
            $from_dcfcf         = empty( $_REQUEST['from_dcfcf'] ) ? false :  esc_sql( $_GET['from_dcfcf'] );
            $to_dcfcf         = empty( $_REQUEST['to_dcfcf'] ) ? false :  esc_sql( $_GET['to_dcfcf'] );
            $status         = empty( $_REQUEST['status'] ) ? false :  esc_sql( $_GET['status'] );
            $cf_post_id  = $this->zt_dcf_form_post_id;
            
            $this->process_bulk_action();
            
            $perPage     = 20;
            if (  ( !empty($from_dcfcf) && !empty($to_dcfcf) ) || !empty($search)  ) 
            {
                if ( isset($status) && ( $status == 'trash') )
                {
                    $totalItems = $wpdb->get_var( 
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $zt_cfdc 
                                WHERE zt_dcf_form_post_id = %d 
                                AND zt_dcf_form_date >= %s AND zt_dcf_form_date <= %s
                                AND zt_dcf_form_delete = %d 
                                AND zt_dcf_form_data LIKE %s",
                                $cf_post_id, $from_dcfcf, $to_dcfcf, 1, '%' . $wpdb->esc_like($search) . '%'
                            )
                        );

                }
                else
                {
                    $totalItems = $wpdb->get_var( 
                                $wpdb->prepare(
                                    "SELECT COUNT(*) FROM $zt_cfdc 
                                    WHERE zt_dcf_form_post_id = %d 
                                    AND zt_dcf_form_date >= %s AND zt_dcf_form_date <= %s
                                    AND zt_dcf_form_delete = %d 
                                    AND zt_dcf_form_data LIKE %s",
                                    $cf_post_id, $from_dcfcf, $to_dcfcf, 0, '%' . $wpdb->esc_like($search) . '%'
                                )
                            );

                }
            }
            else
            {
                if ( isset($status) && ( $status == 'trash') )
                {
                    $totalItems = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $zt_cfdc WHERE zt_dcf_form_post_id = %d AND zt_dcf_form_delete = 1", $cf_post_id ) );
                 }
                else
                {
                    $totalItems = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $zt_cfdc WHERE zt_dcf_form_post_id = %d AND zt_dcf_form_delete = 0 ", $cf_post_id ) );
                }
            }
            $sortable    = $this->get_sortable_columns();
        }
        else
        {
            $perPage     = 10;
            $count_forms = wp_count_posts('wpcf7_contact_form');
            $totalItems  = $count_forms->publish;
        }

        $columns     = $this->get_columns();
        $hidden      = $this->get_hidden_columns();
        $data        = $this->table_data();
        $currentPage = $this->get_pagenum();


        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        if(!empty($sortable)){
            $this->_column_headers = array($columns, $hidden, $sortable );
        }
        else
            $this->_column_headers = array($columns, $hidden );

        $this->items = $data;
    }

    /**
     * Define the sortable columns
     */
    public function get_sortable_columns()
    {
        $s_columns = array (
            'zt_dcf_form_read' => [ 'zt_dcf_form_read', true], 
            'zt_dcf_form_bookmark' => [ 'zt_dcf_form_bookmark', true], 
            'zt_dcf_form_date' => [ 'zt_dcf_form_date', true], 
        );
        return $s_columns;
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? esc_sql($_GET['orderby']) : 'zt_dcf_form_read';
        $order = ( ! empty( $_GET['order'] ) ) ? esc_sql($_GET['order']) : 'desc';

        $result = strcmp( $a[$orderby], $b[$orderby] );
        
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    /**
     * Defines the columns to use in your listing table
     */
    public function get_columns()
    {

        if(isset($this->zt_dcf_form_post_id) && !empty($this->zt_dcf_form_post_id))
        {
            $cf_post_id  = $this->zt_dcf_form_post_id;
            global $wpdb;
            $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
            $zt_cfdc = $ztdb->prefix.'cf_data_center';
            $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

            $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $zt_dcs 
                        WHERE zt_dcf_form_post_id = %d 
                        AND zt_dcf_form_key = %s",
                        $cf_post_id, 'admin_show_hide'
                    ),
                    OBJECT
                );
            
            $first_row            = isset($results[0]) ? unserialize( $results[0]->zt_dcf_form_data ): 0 ;
            $columns              = array();

            if( !empty($first_row) ){

                $columns['cb']      = '<input type="checkbox" />';
                foreach ($first_row as $key => $value) {

                    $key_val       = str_replace( array('_', '-'), ' ', $key);
                    if($value == 'true')
                        $columns[$key] = ucwords(esc_html($key_val));
                    
                    $this->column_titles[] = esc_html($key);
                }
                $columns['zt_dcf_form_read'] = 'Read';
                $columns['zt_dcf_form_bookmark'] = 'Bookmark';
                $columns['zt_dcf_form_date'] = 'Date';

            }
        }
        else
        {
            $columns = array(
                'name' => __( 'Name', ZT_DCFCF_TEXTDOMAIN ),
            );
        }
        
        return $columns;
    }

    /**
     * Define check box for bulk action (each row)
     */
    public function column_cb($item){
        return sprintf(
             '<input type="checkbox" name="%1$s[]" value="%2$s" />',
             $this->_args['singular'],
             $item['zt_dcf_form_id']
        );
    }

    /**
     * Define which columns are hidden
    */
    public function get_hidden_columns()
    {
        if(isset($this->zt_dcf_form_post_id) && !empty($this->zt_dcf_form_post_id))
        {
            return  array('zt_dcf_form_id');
        }
        else
        {
            return array();
        }
    }

    /**
     * Define bulk action
     */
    public function get_bulk_actions() 
    {
        if( isset($this->zt_dcf_form_post_id) && !empty($this->zt_dcf_form_post_id) )
        {
            if(isset($_GET['status']) && ($_GET['status'] == 'trash') )
            {
                return array(
                'restore' => __( 'Restore', ZT_DCFCF_TEXTDOMAIN ),
                'delete' => __( 'Delete', ZT_DCFCF_TEXTDOMAIN ),
                'csv' => __( 'Export CSV', ZT_DCFCF_TEXTDOMAIN ),
                'xls' => __( 'Export XLS', ZT_DCFCF_TEXTDOMAIN ),
                'pdf' => __( 'Export PDF', ZT_DCFCF_TEXTDOMAIN ),
            );
            }
            else
            {
                return array(
                'trash' => __( 'Trash', ZT_DCFCF_TEXTDOMAIN ),
                'csv' => __( 'Export CSV', ZT_DCFCF_TEXTDOMAIN ),
                'xls' => __( 'Export XLS', ZT_DCFCF_TEXTDOMAIN ),
                'pdf' => __( 'Export PDF', ZT_DCFCF_TEXTDOMAIN ),
            );
            }
        }
    }

    /**
     * Get the table data
     *
     */
    private function table_data()
    {
        global $wpdb;
        $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
        $zt_cfdc = $ztdb->prefix.'cf_data_center';
        $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';
        $data         = array();

        $page         = $this->get_pagenum();
        $page         = $page - 1;

        if( isset($this->zt_dcf_form_post_id) && !empty($this->zt_dcf_form_post_id) )
        {
            $form_post_id = $this->zt_dcf_form_post_id;
            $search       = empty( $_REQUEST['s'] ) ? false :  esc_sql( $_GET['s'] );
            $from_dcfcf         = empty( $_REQUEST['from_dcfcf'] ) ? false :  esc_sql( $_GET['from_dcfcf'] );
            $to_dcfcf         = empty( $_REQUEST['to_dcfcf'] ) ? false :  esc_sql( $_GET['to_dcfcf'] );
            $start        = $page * 20;

            $orderby = isset($_GET['orderby']) ? esc_sql($_GET['orderby']) : 'zt_dcf_form_date';
            $order   = isset($_GET['order']) ? esc_sql($_GET['order']) : 'desc';
                        
            $status   = isset($_GET['status']) ? esc_sql($_GET['status']) : '';
           
            if (  ( !empty($from_dcfcf) && !empty($to_dcfcf) ) || !empty($search)  ) 
            {
                if ( isset($status) && ( $status == 'trash') )
                {
                    $results = $ztdb->get_results( 
                        "SELECT * FROM $zt_cfdc 
                        WHERE zt_dcf_form_post_id = '$form_post_id' 
                        AND zt_dcf_form_date >= '$from_dcfcf' AND zt_dcf_form_date <= '$to_dcfcf' 
                        AND zt_dcf_form_data LIKE '%$search%'
                        AND zt_dcf_form_delete = 1
                        ORDER BY $orderby $order 
                        LIMIT $start,20", OBJECT);
                }
                else
                {
                    $results = $ztdb->get_results( 
                        "SELECT * FROM $zt_cfdc 
                        WHERE zt_dcf_form_post_id = '$form_post_id' 
                        AND zt_dcf_form_date >= '$from_dcfcf' AND zt_dcf_form_date <= '$to_dcfcf' 
                        AND zt_dcf_form_data LIKE '%$search%'
                        AND zt_dcf_form_delete = 0 
                        ORDER BY $orderby $order 
                        LIMIT $start,20", OBJECT);
                }
            }
            else
            {
                if ( isset($status) && ( $status == 'trash') )
                {
                    $results = $ztdb->get_results( 
                    "SELECT * FROM $zt_cfdc 
                    WHERE zt_dcf_form_post_id = '$form_post_id'
                    AND zt_dcf_form_delete = 1
                    ORDER BY $orderby $order
                    LIMIT $start,20", OBJECT );
                }
                else
                {
                    $results = $ztdb->get_results( 
                    "SELECT * FROM $zt_cfdc 
                    WHERE zt_dcf_form_post_id = '$form_post_id'
                    AND zt_dcf_form_delete = 0
                    ORDER BY $orderby $order
                    LIMIT $start,20", OBJECT );
                }
            }

            foreach ( $results as $result ) 
            {
                $form_value = unserialize( $result->zt_dcf_form_data );

                    $link  = "<b><a href=admin.php?page=zt-dcfcf-list&fid=%s&ufid=%s>%s</a></b>";
                    $nolink  = "<b><a href='#' data-id=%s data-status=%s>%s</a></b>";

                $fid   = $result->zt_dcf_form_post_id;
                $cf_values['zt_dcf_form_id'] = $result->zt_dcf_form_id;

                foreach ( $this->column_titles as $col_title) 
                {
                    $cf_value[ $col_title ] = isset( $cf_value[ $col_title ] ) ? $cf_value[ $col_title ] : '';
                }

                foreach ($form_value as $k => $value) 
                {
                    $ktmp = $k;

                    if ( is_array($value) || is_object($value) ) 
                    {
                        $val = implode(', ',$value);
                        $cf_values[$ktmp] = ( strlen($val) > 10 ) ? substr($val, 0, 10).'...': $val;
                        $cf_values[$ktmp] = sprintf($link, $fid, $result->zt_dcf_form_id,$cf_values[$ktmp]);
                    }
                    else
                    {
                        $value = strip_tags( $value );

                        // wpcf7_ztdcfcf_uploads checked in value if exist then condition true for file type.
                        if (preg_match('/wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/', $value)) {
                            
                            // Getting the original name
                            $originalName = pathinfo(parse_url($value)['path'], PATHINFO_FILENAME);
                            
                            //get extension here
                            $ext = pathinfo(
                                parse_url($value, PHP_URL_PATH), 
                                PATHINFO_EXTENSION
                            );

                            $cf_values[$ktmp] = $originalName.'.'.$ext;
                            $cf_values[$ktmp] = sprintf($link, $fid, $result->zt_dcf_form_id,$cf_values[$ktmp]);
                        } else {
                            $cf_values[$ktmp] = ( strlen($value) > 10 ) ? substr($value, 0, 10).'...': $value;
                            $cf_values[$ktmp] = sprintf($link, $fid, $result->zt_dcf_form_id,$cf_values[$ktmp]);
                        }

                        
                    }

                }

                if($result->zt_dcf_form_read == 1){
                    $icon = '<span class="dashicons dashicons-buddicons-pm"></span>';
                    $status = 1;
                    $cf_values['zt_dcf_form_read'] = sprintf($nolink, $result->zt_dcf_form_id, $status, $icon);
                }
                else{
                    $icon = '<span class="dashicons dashicons-email-alt"></span>';
                    $status = 0;
                    $cf_values['zt_dcf_form_read'] = sprintf($nolink, $result->zt_dcf_form_id, $status, $icon);
                }
                
                if($result->zt_dcf_form_bookmark == 1){
                    $icon = '<span class="dashicons dashicons-star-filled"></span>';
                    $status = 1;
                    $cf_values['zt_dcf_form_bookmark'] = sprintf($nolink, $result->zt_dcf_form_id, $status, $icon);
                }
                else{
                    $icon = '<span class="dashicons dashicons-star-empty"></span>';
                    $status = 0;
                    $cf_values['zt_dcf_form_bookmark'] = sprintf($nolink, $result->zt_dcf_form_id, $status, $icon);
                }

                $cf_values['zt_dcf_form_date'] = sprintf($link, $fid, $result->zt_dcf_form_id, date("d-m-Y", strtotime($result->zt_dcf_form_date )));
                $data[] = $cf_values;
            }//foreach

        }//if
        else
        {
            $start        = $page * 10;
        
            $args = array(
                'post_type'=> 'wpcf7_contact_form',
                'order'    => 'ASC',
                'posts_per_page' => 10,
                'offset' => $start
            );

            $the_query = new WP_Query( $args );

            while ( $the_query->have_posts() ) : $the_query->the_post();
                $post_id = get_the_id();
                $totalItems   = $ztdb->get_var("SELECT COUNT(*) FROM $zt_cfdc WHERE zt_dcf_form_id = $post_id");
                $title = get_the_title();
                $link  = "<a class='row-title' href=admin.php?page=zt-dcfcf-list&fid=$post_id>%s</a>";
                $data_value['name']  = sprintf( $link, $title );
                $data[] = $data_value;
            endwhile;
        
        }//else

        return $data;
    }

    /**
     * Define bulk action
     *
     */
    public function process_bulk_action(){

        $cf_post_id  = esc_sql( $_GET['fid'] );
        $post_7 = get_post( $cf_post_id ); 
        $title = $post_7->post_title;

        global $wpdb;
        $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
        $zt_cfdc = $ztdb->prefix.'cf_data_center';
        $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';

        $action     = $this->current_action();

        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) 
        {
            $nonce        = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $nonce_action = 'bulk-' . $this->_args['plural'];

            if ( !wp_verify_nonce( $nonce, $nonce_action ) )
            {
                wp_die( 'Not valid..!!' );
            }
        }

        if( 'trash' === $action ) 
        {
            if(empty($_POST['zt_dcf_form']) || !isset($_POST['zt_dcf_form']))
            {
                ?>
                    <script type="text/javascript">alert("Please Select Data!"); </script>
                <?php
            }
            else
            {   
                $status  = (isset( $_GET['status'] ) && !empty( $_GET['status'] )) ? esc_sql( $_GET['status'] ) : "";
                $cf_ids = esc_sql( $_POST['zt_dcf_form'] );             
                
                foreach ($cf_ids as $cf_id):
                    $exe_query = $ztdb->query( "UPDATE $zt_cfdc SET zt_dcf_form_delete = 1 WHERE zt_dcf_form_id = $cf_id" );
                endforeach;

                if(1 === $exe_query)
                {
                    ?><script type="text/javascript">alert("Data Trashed Successfully!"); </script><?php
                }
            }
        }
        if( 'restore' === $action ) 
        {
            if(empty($_POST['zt_dcf_form']) || !isset($_POST['zt_dcf_form']))
            {
                ?>
                    <script type="text/javascript">alert("Please Select Data!"); </script>
                <?php
            }
            else
            {                
                $status  = esc_sql( $_GET['status'] );
                $cf_ids = esc_sql( $_POST['zt_dcf_form'] );

                foreach ($cf_ids as $cf_id):
                    $exe_query = $ztdb->query( "UPDATE $zt_cfdc SET zt_dcf_form_delete = 0 WHERE zt_dcf_form_id = $cf_id" );
                endforeach;

                if(1 === $exe_query)
                {
                    ?><script type="text/javascript">alert("Data Restored Successfully!"); </script><?php
                }
            }
        }
        else if( 'delete' === $action ) 
        {
            if(empty($_POST['zt_dcf_form']) || !isset($_POST['zt_dcf_form']))
            {
                ?>
                    <script type="text/javascript">alert("Please Select Data!"); </script>
                <?php
            }
            else
            {
                $status  = esc_sql( $_GET['status'] );
                $cf_ids = esc_sql( $_POST['zt_dcf_form'] );

                foreach ($cf_ids as $cf_id):

                    /* start delete file in directory */ 
                    $resultsData = $ztdb->get_results("SELECT zt_dcf_form_data FROM $zt_cfdc WHERE zt_dcf_form_id = '$cf_id'", OBJECT);
                    
                    $fileResults = unserialize($resultsData[0]->zt_dcf_form_data);
                    //empty checked all data
                    if(isset($fileResults) && !empty($fileResults)){
                        
                        //directory name
                        $upload_dir = wp_upload_dir();
                        
                        $directory_zt_dcfcf ='wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/';
                        $newPath = $upload_dir['basedir'].'/'.$directory_zt_dcfcf;
                        //$newPath = $upload_dir['baseurl'].'/'.$directory_zt_dcfcf;
                        
                        //with out array value get all data 
                        $arrDataFiles = array();
                        foreach ($fileResults as $keyArray => $valueArray) {
                            //removed checkedbox, radio fields and make new array.
                            if(!is_array($valueArray)){
                                $arrDataFiles[$keyArray] = $valueArray;
                            }
                        }

                        //directory string name checked in array
                        $fileKeysArrs = preg_grep('/wpcf7_'.ZT_DCFCF_PREFIX.'_uploads/', $arrDataFiles);
                        //$fileKeysArrs = preg_grep('/wpcf7_ztdcfcf_uploads/', $fileResults);
                        
                        foreach ($fileKeysArrs as $key => $value) {
                            //checked value empty or not
                            if(!empty($value)){
                                
                                //ger href value form ancher tag
                                $fileDatas = new SimpleXMLElement($value);
                                
                                $filePath = $fileDatas[0]->attributes()['href'];
                                
                                // Getting the original name
                                $originalName = pathinfo(parse_url($filePath)['path'], PATHINFO_FILENAME);
                                
                                //get extension here
                                $ext = pathinfo(
                                    parse_url($filePath, PHP_URL_PATH), 
                                    PATHINFO_EXTENSION
                                );

                                $newFileName = $originalName.'.'.$ext;
                                
                                //directroy file path
                                $fileURL  = $newPath.$newFileName;
                                
                                //checked url data here
                                if(!empty($fileURL)){
                                    //file exist condition checked here
                                    if (file_exists($fileURL)) {
                                        //delete file here
                                        unlink($fileURL);
                                    }
                                }
                            }//closed checked value empty or not
                        } //closed foreach loop                      
                    } //closed empty checked all data                   
                    /* end delete file in directory */

                    $exe_query = $ztdb->delete( $zt_cfdc , array( 'zt_dcf_form_id' => $cf_id ), array( '%d' ) );
                endforeach;

                if(TRUE === $exe_query)
                {
                    ?><script type="text/javascript">alert("Data Trashed Successfully!"); </script><?php
                }
            }
        }
        else if( 'xls' === $action )
        {
            if(empty($_POST['zt_dcf_form']) || !isset($_POST['zt_dcf_form']))
            {
                ?>
                    <script type="text/javascript">alert("Please Select Data!"); </script>
                <?php
            }
            else
            {                
                $cf_ids = esc_sql( $_POST['zt_dcf_form'] );

                $data = $this->get_column_data($cf_ids);
                $show_col = $this->show_admin_column();

                foreach ($data as $array => $key) 
                {
                    foreach ($key as $column => $value) 
                    {
                        if(array_key_exists($column, $show_col))
                        {
                            $xls_records[$array][$column] = strip_tags($value);
                        }
                    }
                }
                if (!class_exists('XLSXWriter')) {
                  require_once('class.ztdcfcf.xlsxwriter.php');
                }
                $fileLocation = $title.".xlsx";

                header('Content-Description: File Transfer');
                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                header("Content-Disposition: attachment; filename=" . basename($fileLocation));
                header("Content-Transfer-Encoding: binary");
                header("Expires: 0");
                header("Pragma: public");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header('Content-Length: ' . filesize($fileLocation));
				
                array_unshift($xls_records,$show_col);

                $writer = new XLSXWriter();
                $writer->writeSheet($xls_records);
                $writer->writeToFile($fileLocation);

                ob_clean();
                readfile($fileLocation);
                unlink($fileLocation);
                
                die();
            }
        }
        else if( 'csv' === $action )
        {
            if(empty($_POST['zt_dcf_form']) || !isset($_POST['zt_dcf_form']))
            {
                ?>
                    <script type="text/javascript">alert("Please Select Data!"); </script>
                <?php
            }
            else
            {
                $cf_ids = esc_sql( $_POST['zt_dcf_form'] );

                $data = $this->get_column_data($cf_ids);
                $show_col = $this->show_admin_column();
                
                ob_clean();
                ob_start();
                $df = fopen("php://output", 'w');

                fputs( $df, ( chr(0xEF) . chr(0xBB) . chr(0xBF) ) );

                if(isset($_GET['status']) && $_GET['status'] == 'trash' ) 
                    $this->download_send_headers("Trash ".$title.".csv" );
                else
                    $this->download_send_headers($title.".csv" );
            
                fputcsv( $df, $show_col );

                foreach ($data as $array => $key) 
                {
                    foreach ($key as $column => $value) 
                    {
                        if(array_key_exists($column, $show_col))
                        {
                            $csv_records[$column] = strip_tags($value);
                        }
                    }
                            fputcsv( $df, $csv_records);
                }

                echo ob_get_clean();
                fclose( $df );
                die();
            }
        }
        else if( 'pdf' === $action )
        {
            if(empty($_POST['zt_dcf_form']) || !isset($_POST['zt_dcf_form']))
            {
                ?>
                    <script type="text/javascript">alert("Please Select Data!"); </script>
                <?php
            }
            else
            {
                $cf_ids = esc_sql( $_POST['zt_dcf_form'] );

                ob_clean();
                    //flush();
                    ob_start();
                require(plugin_dir_path( __FILE__ ).'assets/fpdf/fpdf.php'); 
                require(plugin_dir_path( __FILE__ ).'assets/fpdf/exfpdf.php'); 
                require(plugin_dir_path( __FILE__ ).'assets/fpdf/easyTable.php'); 
               
                $pdf=new exFPDF();
                $pdf->AddPage(); 
                $pdf->SetFont('helvetica','',10);
                $data = $this->get_column_data($cf_ids);
                $show_col = $this->show_admin_column();

                $count_col = count($show_col);
                $table=new easyTable($pdf, $count_col+1,'align:C{LCRR};border:1; border-color:#a1a1a1; ');

                $table->rowStyle('align:{CCCR};valign:M;bgcolor:#000000; font-color:#ffffff; font-family:times; font-style:B;');
                foreach ($show_col as $key => $value) 
                {
                    $table->easyCell(esc_html($value));
                }
                $table->printRow();
                foreach ($data as $array => $key) 
                {
                    foreach ($key as $column => $value) 
                    {
                        if(array_key_exists($column, $show_col))
                        {
                            $table->easyCell(strip_tags($value));
                        }
                    }
                    $table->printRow();
                }
                $table->endTable();
                
                if(isset($_GET['status']) && $_GET['status'] == 'trash' )
                    $pdf->Output("Trash ".$title.".pdf", 'D'); 
                else
                    $pdf->Output($title.".pdf", 'D'); 
                    
                ob_end_flush();
            }
        }
        else
        {        }
    }

    /* Get columns for display in files */
    function show_admin_column()
    {
        global $wpdb;
        $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
        $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';
        $cf_post_id  = esc_sql( $_GET['fid'] );

        $results = $ztdb->get_results("SELECT * FROM $zt_dcs WHERE zt_dcf_form_post_id = '$cf_post_id' AND zt_dcf_form_key = 'admin_show_hide'", OBJECT);
        $first_row            = isset($results[0]) ? unserialize( $results[0]->zt_dcf_form_data ): 0 ;

        $show_col              = array();

        if( !empty($first_row) )
        {
            foreach ($first_row as $key => $value) 
            {                
                $key_val       = ucwords(str_replace( array('_', '-'), ' ', $key));
                if($value == "true")
                    $show_col[$key] = $key_val;
            }
            $show_col['date'] = "Date";
            return $show_col;
        }
    }

    /* Get submitted entries data for display in files */
    function get_column_data($cf_ids)
    {
        global $wpdb, $show_col;
        $ztdb       = apply_filters( 'zt_dcfcf_database', $wpdb );
        $zt_cfdc = $ztdb->prefix.'cf_data_center';
        $zt_dcs  = $ztdb->prefix.'cf_data_center_settings';
        $cf_post_id  = esc_sql( $_GET['fid'] );
        $cf_ids = esc_sql( $cf_ids );

        $trash  = (isset( $_GET['status'] ) && !empty( $_GET['status'] )) ? esc_sql( $_GET['status'] ) : "";
        $status = ($trash == "trash") ? 1 : 0;  

        $results = $ztdb->get_results("SELECT * FROM $zt_dcs WHERE zt_dcf_form_post_id = '$cf_post_id' AND zt_dcf_form_key = 'admin_show_hide' ", OBJECT);
        $first_row            = isset($results[0]) ? unserialize( $results[0]->zt_dcf_form_data ): 0 ;

        $data_array              = array();

        if( !empty($first_row) )
        {
            foreach ($first_row as $key => $value) 
            {
                $data_array[$key] = esc_html($key);
            }
        }

        if(!empty($cf_ids))
        {
            foreach ($cf_ids as $cf_id) 
            {
                $resultall = $ztdb->get_results( "SELECT * FROM $zt_cfdc WHERE zt_dcf_form_id = '$cf_id' AND zt_dcf_form_delete = $status ORDER BY zt_dcf_form_id DESC", OBJECT );

                    foreach ( $resultall as $result ) 
                    {
                        $form_value = unserialize( $result->zt_dcf_form_data );
                        foreach ($form_value as $k => $value) 
                        {
                            if ( is_array($value) ) 
                            {
                                $arr_str_data =  implode(', ',$value);
                                $data_array[$k] = trim(esc_html( $arr_str_data ));
                            }
                            else
                            {
                                $data_array[$k] = trim( $value );
                            }
                        }
                        $data_array['date'] =  date("d-m-Y", strtotime($result->zt_dcf_form_date ));
                    }
                        $data[] = $data_array;
            }
            return $data;
        }
        else
        {
            $resultall = $ztdb->get_results( "SELECT * FROM $zt_cfdc WHERE zt_dcf_form_post_id = '$cf_post_id' AND zt_dcf_form_delete = $status ORDER BY zt_dcf_form_id DESC", OBJECT );
        
            if( !empty($resultall) )
            {
                foreach ( $resultall as $result ) 
                {
                    $form_value = unserialize( $result->zt_dcf_form_data );
                    foreach ($form_value as $k => $value) 
                    {
                        if ( is_array($value) ) 
                        {
                            $arr_str_data =  implode(', ',$value);
                            $data_array[$k] = trim(esc_html( $arr_str_data ));
                        }
                        else
                        {
                            $data_array[$k] = trim(esc_html( $value ));
                        }
                    }
                    $data_array['date'] =  date("d-m-Y", strtotime($result->zt_dcf_form_date ));
                    $data[] = $data_array;
                }
                return $data;
            }
        }
    }

    public function download_send_headers( $filename ) {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: 0");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

    }

    /**
     * Define what data to show on each column of the table
     */
    public function column_default( $item, $column_name )
    {
        return $item[ $column_name ];
    }

}