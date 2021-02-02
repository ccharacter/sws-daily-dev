<?php



add_action( 'init', 'dev_book' );

function dev_book() {
    register_post_type( 'dev_book',
        array(
            'labels' => array(
                'name' => 'Source Books',
                'singular_name' => 'Book',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Book',
                'edit' => 'Edit',
                'edit_item' => 'Edit Book',
                'new_item' => 'New Book',
                'view' => 'View',
                'view_item' => 'View Book',
                'search_items' => 'Search Books',
                'not_found' => 'No Devotional Books found',
                'not_found_in_trash' => 'No Devotional Books found in Trash'
            ),
 
            'public' => true,
			'capability_type' => 'page',
            'rewrite' => array(
				'slug' => 'book'
			),
			'supports' => array('title','editor','thumbnail'),
            'has_archive' => true,
			'show_in_menu' => 'edit.php?post_type=dev_reading'
		)
    );
}



// add meta box for book
function add_dev_book_meta_box() {
	add_meta_box(
		'dev_book_meta_box', // $id
		'Details', // $title
		'show_dev_book_meta_box', // $callback
		'dev_book', // $screen
		'side', // $context
		'high' // $priority
	);
}
add_action( 'add_meta_boxes', 'add_dev_book_meta_box' );



// show content in meta box
function show_dev_book_meta_box() {
	global $post; global $fieldArr2;  
		$meta = get_post_meta( $post->ID, 'b_author', true ); ?>
	<input type="hidden" name="b_details_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
<?php 
	foreach ($fieldArr2 as $fld=>$label) {
		$val=get_post_meta($post->ID, $fld,true); if (!$val) { $val="";}
		echo "<div class='acf-label'>".$label."</div><div class='acf-input'><input type='text' name='".$fld."' value='".$val."' /></div>";
	}
}


// Add the custom columns to the book post type:
add_filter( 'manage_dev_book_posts_columns', 'set_custom_edit_dev_book_columns' );
function set_custom_edit_dev_book_columns($columns) {
    unset( $columns['author'] );
    unset( $columns['date'] );
    $columns['b_author'] = __( 'Author', 'your_text_domain' );
    $columns['b_publisher'] = __( 'Publisher', 'your_text_domain' );
    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_dev_book_posts_custom_column' , 'custom_dev_book_column', 10, 2 );
function custom_dev_book_column( $column, $post_id ) {
    
	if ($column=="b_author") {
            echo get_post_meta( $post_id , 'b_auth_fname' , true )." ".get_post_meta( $post_id , 'b_auth_lname' , true ); 
	}
	if ($column=="b_publisher") {
            echo get_post_meta( $post_id , 'b_publisher' , true ); 
	}

}



// save meta data
function save_dev_book_details_meta( $post_id ) {   
	
	// verify nonce
	if ( !wp_verify_nonce( $_POST['b_details_nonce'], basename(__FILE__) ) ) {
		return $post_id; 
	}
	global $fieldArr2;
		
	foreach ($fieldArr2 as $fld=>$label) {
		$old = get_post_meta( $post_id, $fld, true );
		$new = $_POST[$fld]; //echo $new."|".$old."<br />";
	
		if ( $new && $new !== $old ) {
			update_post_meta( $post_id, $fld, $new );
		} elseif ( '' === $new && $old ) {
			delete_post_meta( $post_id, $fld, $old );
		}
	}		

}
add_action( 'save_post_dev_book', 'save_dev_book_details_meta', 10, 2 );

function dev_book_import_notice($views) {
	
	global $wpdb;
	
    $query = $wpdb->prepare(
        'SELECT ID FROM ' . $wpdb->posts . '
        WHERE post_status=%s
		AND post_type = %s',
        "publish","dev_book"
    );
	$wpdb->query( $query );

    if (($wpdb) && ( $wpdb->num_rows ==0 )) {
  		echo "<p><strong><a href='/daily-devotional'>CLICK HERE</a></strong> to import sample devotional books and readings.</p>";
	}
  
  return $views;
}
add_filter('views_edit-dev_book','dev_book_import_notice');

?>