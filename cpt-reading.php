<?php


add_action( 'init', 'dev_reading' );

function dev_reading() {
    register_post_type( 'dev_reading',
        array(
            'labels' => array(
                'name' => 'Devotional Readings',
                'singular_name' => 'Reading',
                'add_new' => 'Add New Reading',
                'add_new_item' => 'Add New Reading',
                'edit' => 'Edit',
                'edit_item' => 'Edit Reading',
                'new_item' => 'New Reading',
                'view' => 'View',
                'view_item' => 'View Reading',
                'search_items' => 'Search Readings',
                'not_found' => 'No Devotional Readings found',
                'not_found_in_trash' => 'No Devotional Readings found in Trash'
            ),
 
            'public' => true,
			'capability_type' => 'page',
            'menu_position' => 8,
            'supports' => array( 'editor', 'title' ),
            'rewrite' => array(
			'slug' => 'reading'
			),
			//'taxonomies' => array( 'devotional_books' ),
            'menu_icon' => 'dashicons-book',
            'has_archive' => false
		)
    );
}


// add meta box for date
function add_dev_date_meta_box() {
	add_meta_box(
		'dev_date_meta_box', // $id
		'Reading Details', // $title
		'show_dev_date_meta_box', // $callback
		'dev_reading', // $screen
		'side', // $context
		'high' // $priority
	);
}
add_action( 'add_meta_boxes', 'add_dev_date_meta_box' );


// show content in date meta box
function show_dev_date_meta_box() {
	global $post;  
		$meta = get_post_meta( $post->ID, 'dev_date', true ); 
		
		global $fieldArr;
		foreach ($fieldArr as $fld=>$label) {
			${$fld}=get_post_meta($post->ID,$fld,true);	
		}
		?>

	<input type="hidden" name="r_details_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
    Source Book<br /><select name='r_src'><option>CHOOSE</option>
    <?php	
	$tmp=sws_loop_dev_books();
    while ($tmp->have_posts()) : $tmp->the_post();
		$id=get_the_id(); $title=get_the_title();
		if ($r_src==$id) { $sel="selected";} else {$sel="";}
	   echo "<option value='$id' $sel>$title</option>";
	endwhile;

	?>
	</select>
	<table><tr><td>Month</td><td>Day</td></tr>
    <tr><td> <select name='r_month'><option>CHOOSE</option>
	<?php for ($k=1; $k<13; $k++) {
		if ($r_month==$k) { $tmp="selected"; } else {$tmp="";}
		echo "<option value='$k' $tmp>".date('M', mktime(0, 0, 0, $k, 10))."</option>";
	}?>
	</select></td><td><select name='r_day'><option>CHOOSE</option>
	<?php for ($k=1; $k<32; $k++) {
		if ($r_day==$k) { $tmp="selected"; } else {$tmp="";}
		echo "<option $tmp>$k</option>";
	}?>
	</select></td></tr></table>
	<hr />Alternate (optional)<table><tr><td>Month</td><td>Day</td></tr>
    <tr><td> <select name='r_month2'><option>CHOOSE</option>
	<?php for ($k=1; $k<13; $k++) {
		if ($r_month2==$k) { $tmp="selected"; } else {$tmp="";}
		echo "<option value='$k' $tmp>".date('M', mktime(0, 0, 0, $k, 10))."</option>";
	}?>
	</select></td><td><select name='r_day2'><option>CHOOSE</option>
	<?php for ($k=1; $k<32; $k++) {
		if ($r_day2==$k) { $tmp="selected"; } else {$tmp="";}
		echo "<option $tmp>$k</option>";
	}?>
	</select></td></tr></table>
	<?php 
}


// Add the custom columns 
add_filter( 'manage_dev_reading_posts_columns', 'set_custom_edit_dev_reading_columns' );
function set_custom_edit_dev_reading_columns($columns) {
    unset( $columns['author'] );
    unset( $columns['date'] );
    $columns['r_month'] = __( 'Date', 'your_text_domain' );
    $columns['r_month2'] = __( 'Date (alt)', 'your_text_domain' );
	$columns['r_src'] =__( 'Source Book', 'your_text_domain' );
    return $columns;
}


// Add the data to the custom columns for the book post type:
add_action( 'manage_dev_reading_posts_custom_column' , 'custom_dev_reading_column', 10, 2 );
function custom_dev_reading_column( $column, $post_id ) {
    $tmp=array(); global $fieldArr;
	foreach ($fieldArr as $fld=>$label) {
		$tmp[$fld]=get_post_meta($post_id,$fld,true);	
	}
	$script=get_post_meta($post_id,'r_script',true);
	switch ( $column ) {

        case 'r_month' :
			if ($script=="YES") { echo "SAMPLE"; } else {
				if (($tmp['r_month']==0) && ($tmp['r_day']==0)) { echo "--";} else {
					echo date('M', mktime(0, 0, 0, $tmp['r_month'], 10))." ".$tmp['r_day'];
				}
			}
	        break;

        case 'r_month2' :
			if (($tmp['r_month2']==0) && ($tmp['r_day2']==0)) { echo "--";} else {
	            echo date('M', mktime(0, 0, 0, $tmp['r_month2'], 10))." ".$tmp['r_day2'];
			}
	        break;
			
		case 'r_src' :
            echo get_the_title($tmp['r_src']); 
            break;
    }
}


// save  meta data
function save_dev_reading_details_meta( $post_id ) {   

	// verify nonce
	if ( wp_verify_nonce( $_POST['r_details_nonce'], basename(__FILE__) ) ) {
	
		global $fieldArr;
			
		foreach ($fieldArr as $fld=>$label) {
			$old = get_post_meta( $post_id, $fld, true );
			$new = $_POST[$fld];
		
			if ( $new && $new !== $old ) {
				update_post_meta( $post_id, $fld, $new );
			} elseif ( '' === $new && $old ) {
				delete_post_meta( $post_id, $fld, $old );
			}
		}
	} else { 	return $post_id; }	
}
add_action( 'save_post_dev_reading', 'save_dev_reading_details_meta', 10, 2 );

function dev_reading_import_notice($views) {
	
		global $wpdb;
	
    $query = $wpdb->prepare(
        'SELECT ID FROM ' . $wpdb->posts . '
        WHERE post_status=%s
		AND post_type = %s',
        "publish","dev_reading"
	);
	$wpdb->query( $query );

    if (($wpdb) && ( $wpdb->num_rows ==0 )) {
		  echo "<p><strong><a href='/daily-devotional'>CLICK HERE</a></strong> to import sample devotional books and readings.</p>";
	} else {
		  echo "<p>If no reading is specified for any given date, one of the sample readings will be randomly chosen, if they have been imported. <br />To re-import the sample readings, move any existing readings to the TRASH (you can easily restore them after the import runs).</p>";		
	}
  return $views;
}
add_filter('views_edit-dev_reading','dev_reading_import_notice');


add_filter( 'parse_query', 'dev_reading_prefix_parse_filter' );
function  dev_reading_prefix_parse_filter($query) {
   global $pagenow;
   $current_page = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

   if ( is_admin() && 
     'dev_reading' == $current_page &&
     'edit.php' == $pagenow && 
      isset( $_GET['r_src'] ) && 
      $_GET['r_src'] != '') {

    $myVal = $_GET['r_src'];
    $query->query_vars['meta_key'] = 'r_src';
    $query->query_vars['meta_value'] = $myVal;
    $query->query_vars['meta_compare'] = '=';
  }
}
/**
 * Add extra dropdowns to the List Tables
 *
 * @param required string $post_type    The Post Type that is being displayed
 */
add_action('restrict_manage_posts', 'dev_reading_add_extra_tablenav');
function dev_reading_add_extra_tablenav($post_type){

    global $wpdb;

    /** Ensure this is the correct Post Type*/
    if($post_type !== 'dev_reading')
        return;

    /** Output the dropdown menu */
    echo '<select class="" id="r_src" name="r_src"><option>All Books</option>';

   	$tmp=sws_loop_dev_books();
    while ($tmp->have_posts()) : $tmp->the_post();
		$id=get_the_id(); $title=get_the_title();
	   echo "<option value='$id'>$title</option>";
	endwhile;

	
	echo '</select>';

}



?>