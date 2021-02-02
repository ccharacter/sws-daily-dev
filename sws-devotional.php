<?php
/*
Plugin Name: ZZ Devotional (Sharon)
Plugin URI: http://www.ccharacter.com/
Description: Daily Devotional module.
Version: 1.0
Author: Sharon Stromberg
Author URI: http://www.ccharacter.com
License: GPLv2
*/

//session_start();

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_filter('widget_text', 'do_shortcode');

date_default_timezone_set('America/New_York');

$fieldArr=array("r_month"=>"Month","r_day"=>"Day","r_month2"=>"Month","r_day2"=>"Day","r_src"=>"Source Book"); 
$fieldArr2=array("b_auth_fname"=>"Author Firstname","b_auth_lname"=>"Author Lastname","b_publisher"=>"Publisher","b_copyright"=>"Copyright","b_link"=>"Link to Purchase");

require_once("cpt-book.php");
require_once("cpt-reading.php");
require_once("add_my_template.php");


function show_devotional_link($id) {
	echo "<hr /><p>from <strong><em>".get_the_title($id)."</em></strong><br />";
	echo get_post_meta($id, 'b_auth_fname',true)." ".get_post_meta($id, 'b_auth_lname',true)." | ";		
	echo get_post_meta($id, 'b_publisher',true)." (".get_post_meta($id, 'b_copyright',true).")<br />";
	echo "<a href='".get_post_meta($id,'b_link',true)."' target='_blank'>Purchase this book</a></p>";
		
}

function load_devotional_template($template) {
    global $post;

    if ($post->post_type == "dev_reading" && $template !== locate_template(array("single-devotional.php"))){
        /* This is a custom post 
         * AND a 'single template' is not found on 
         * theme or child theme directories, so load it 
         * from our plugin directory
         */
        return plugin_dir_path( __FILE__ ) . "single-devotional.php";
    }

    if ($post->post_type == "dev_book" && $template !== locate_template(array("single-book.php"))){
        /* This is a custom post 
         * AND a 'single template' is not found on 
         * theme or child theme directories, so load it 
         * from our plugin directory
         */
        return plugin_dir_path( __FILE__ ) . "single-book.php";
    }

    return $template;
}
add_filter('single_template', 'load_devotional_template',99);


function sws_ck_post_exists( $post_title, $post_type) {
	
	global $wpdb;
	
    $query = $wpdb->prepare(
        'SELECT ID FROM ' . $wpdb->posts . '
        WHERE post_title = %s
		AND post_status = %s
        AND post_type = %s',
        $post_title, "publish", $post_type
    );
	$wpdb->query( $query );

    if (($wpdb) && ( $wpdb->num_rows ==0 )) { return false; }
	return true;
}

if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => 'Devotional Sidebar (Top)',
	'id'=> 'devotional_top',
    'before_widget' => '<div class = "widgetizedArea">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>',
  )
);

if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => 'Devotional Sidebar (Bottom)',
	'id'=>'devotional_bottom',
    'before_widget' => '<div class = "widgetizedArea">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>',
  )
);

function sws_loop_dev_books() {
	$args = array( 
		'orderby' => 'title', 'order'=> 'ASC',
		'post_type' => 'dev_book'
	);
	$tmp=new WP_Query($args);
	return $tmp;	
   	wp_reset_postdata();	
}


// CREATE SHORTCODE: List books
function dev_list_books_func( $atts ){
	echo "<ul>"; $this_url=get_permalink();
	$post_type=get_post_type();
	
	$tmp=sws_loop_dev_books();
    while ($tmp->have_posts()) : $tmp->the_post();
		$id=get_the_id(); $title=get_the_title(); $url=get_permalink();
		if (! (($post_type=="dev_book") && ($this_url==$url)) ) { 
			echo "<li><a href='$url'>$title</a></li>";
		}
	endwhile;

	echo "</ul>";
}
add_shortcode( 'dev_list_books', 'dev_list_books_func' );

// CREATE SHORTCODE: List other readings for this date
function dev_other_readings_func( $atts ) {
	if (get_post_type()=='page') { // it's the daily version, not single

	global $devArr; 
	
	// EDIT LINK
	if (current_user_can('administrator')) {
		echo "<p><strong><a href='/wp-admin/post.php?post=".$devArr[0]."&action=edit' target='_blank'>EDIT THIS READING</a></strong></p>";
	}
	
		if ((is_array($devArr)) && (count($devArr)>1)) { 	
			echo "<div class='text article__body spacing'><h3>Further Reading for Today</h3><p>";
			foreach ($devArr as $key=>$r) { if ($key>0) {
				echo "<a href='/daily-devotional/?id=$r'>".get_the_title($r)."</a><br />";
			} }
			echo "</p></div>";
		}
	}
}
add_shortcode ( 'dev_other_readings', 'dev_other_readings_func');
add_filter( 'widget_text', 'do_shortcode' );

// CREATE DEFAULT PAGE
function devotional_defaults() {
	if (!(sws_ck_post_exists("Daily Devotional","page"))) {
			
		$arr=array("post_type"=>"page","post_status"=>"publish","post_content"=>"[sws_daily_devotional]","post_title"=>"Daily Devotional",  'page_template'  => 'single-devotional.php');
		$id=wp_insert_post($arr);
		
		// INTRO
		update_post_meta($id,'intro',"An inspirational reading and Scripture passage to start your day off right");
		// THUMBNAIL	
		$image_url = plugins_url( 'img/1526049-SM.jpg', __FILE__); //echo $image_url;
		
		$upload_dir = wp_upload_dir();
		
		$image_data = file_get_contents( $image_url );
		
		$filename = basename( $image_url );
		
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
		  $file = $upload_dir['path'] . '/' . $filename;
		}
		else {
		  $file = $upload_dir['basedir'] . '/' . $filename;
		}
		
		file_put_contents( $file, $image_data );
		
		$wp_filetype = wp_check_filetype( $filename, null );
		
		$attachment = array(
		  'post_mime_type' => $wp_filetype['type'],
		  'post_title' => sanitize_file_name( $filename ),
		  'post_content' => '',
		  'post_status' => 'inherit'
		);
		
		$attach_id = wp_insert_attachment( $attachment, $file, $id);
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( $id, $attach_id );

	}
	// add to admin	
   	global $submenu;
    $permalink = '/daily-devotional';
    $submenu['edit.php?post_type=dev_reading'][] = array( 'Today\'s Devotional', 'manage_options', $permalink );

    flush_rewrite_rules();
}
add_action('admin_menu', 'devotional_defaults');

// insert initial data
function devotional_import_default() {
	global $wpdb;
	
	// if there are no dev_books	
    $query = $wpdb->prepare(
        'SELECT ID FROM ' . $wpdb->posts . '
        WHERE post_status=%s
		AND post_type = %s',
        "publish","dev_book"
    );
	$wpdb->query( $query );
    if (($wpdb) && ( $wpdb->num_rows ==0 )) { 
		require_once('data/importB.php');	
		
		// CREATE DEFAULT WIDGET CONTENT	
		wpse_sws_dev_pre_set_widget( 'devotional_top', 'custom_html',
			array(
				'title' => 'We Also Recommend...',
				'text' => 'test text',
				'filter' => false,
			)
		);
		
		wpse_sws_dev_pre_set_widget( 'devotional_top', 'custom_html',
			array(
				'title' => 'TEST2',
				'text' => "[dev_list_books]",
				'filter' => false,
			)
		);
				
		wpse_sws_dev_pre_set_widget( 'devotional_bottom', 'custom_html',
			array(
				'title' => 'TEST',
				'text' => "[dev_other_readings]",
				'filter' => false,
			)
		);

		
	}
	
	// if there are no individual readings ($fldArr)
    $query = $wpdb->prepare(
        'SELECT ID FROM ' . $wpdb->posts . '
        WHERE post_status=%s
		AND post_type = %s',
        "publish","dev_reading"
    );
	$wpdb->query( $query );
    if (($wpdb) && ( $wpdb->num_rows ==0 )) { 
		require_once('data/import.php');
	}
}



function dev_get_daily($t=0,$month="X",$day="X") {
	
    //$timezone = $_SESSION['time'];
	if (($month=="X") && ($day=="X")) {
	$month=date("n",strtotime(current_time( 'mysql' )));
	$day=date("j",strtotime(current_time( 'mysql' )));
	}
	$myDate=$month."|".$day;
	$retArr=array();

	$args = array(
		'post_type'  => 'dev_reading',
		'post_status'=> 'publish',
		'orderby'=>'rand',
		'order'=>'DESC',
		'meta_query' => array(
			'relation' => 'OR',
			array(
					'relation' => 'AND',
					array('key' => 'r_month','value' => $month),
					array('key' => 'r_day','value' => $day)
			),
			array(
					'relation' => 'AND',
					array('key' => 'r_month2','value' => $month),
					array('key' => 'r_day2','value' => $day)
			),
			array(
					'relation' => 'AND',
					array('key' => 'r_used','value' => $month."|".$day)
			)
		),
	);
	$search_query = new WP_Query( $args ); //echo $search_query->request;
	if ( $search_query->have_posts() ) { 
		while( $search_query->have_posts() ) {
			$search_query->the_post();
			$myArr[]=get_the_id();
		}
		
		if (!($t==0)) { 
			$retArr[]=$t;
			foreach ($myArr as $tmp) { 
				if (!($tmp==$t)) { $retArr[]=$tmp;}
			}
		} else {$retArr=$myArr;}
	} else {
		wp_reset_postdata();	
		
		$args = array(
			'post_type'  => 'dev_reading',
			'orderby'=>'rand',
			'order'=>'DESC',
			'posts_per_page'=>1,
			'meta_query' => array(
				array(
						'relation' => 'AND',
						array('key' => 'r_month','value' => '0'),
						array('key' => 'r_day','value' => '0'),
						array('key' => 'r_month','value' => '0'),
						array('key' => 'r_day','value' => '0'),
						array('key' => 'r_script','value' => 'YES'),
						array('key' => 'r_used','value' => '0')
				)
			)
		);
			
		$search_query = new WP_Query( $args ); // echo $search_query->request; 
		
		if ( $search_query->have_posts() ) {
			while( $search_query->have_posts() ) {
				$search_query->the_post();
			   	$myID=get_the_id(); //echo $myID;
			   	$retArr[]=$myID;
			  	update_post_meta($myID,'r_used',$month."|".$day);
			}
		} else { // NONE UNUSED
			global $wpdb;
			$lastMonth=date("n",strtotime("-1 month"));
			    $query = $wpdb->prepare(
        		'UPDATE ' . $wpdb->postmeta . '
				set meta_value=0 where meta_key="r_used" and not (meta_value like "%s" or meta_value like "%s")',
				$month."|%",$lastMonth."|%");
				$wpdb->query( $query );

			$search_query = new WP_Query( $args ); // echo $search_query->request; 
			
			if ( $search_query->have_posts() ) {
				while( $search_query->have_posts() ) {
					$search_query->the_post();
					$myID=get_the_id(); //echo $myID;
					$retArr[]=$myID;
					update_post_meta($myID,'r_used',$month."|".$day);
				}
			}
		}
	}
	
	wp_reset_postdata();	
	
	return $retArr;
}


function dev_display($id,$month="X",$day="X") {
	
	date_default_timezone_set('America/New_York');

	if (($month=="X") && ($day=="X")) {
	$month=date("F",strtotime(current_time( 'mysql' )));
	$day=date("j",strtotime(current_time( 'mysql' )));
	}

	$post=get_post($id);
	
	echo "<h2>$month $day: ".$post->post_title."</h3>";
	echo wpautop($post->post_content);	
		
	show_devotional_link(get_post_meta($id,'r_src',true)); 	
}


/**
 * Pre-configure and save a widget, designed for plugin and theme activation.
 * 
 * @link    http://wordpress.stackexchange.com/q/138242/1685
 *
 * @param   string  $sidebar    The database name of the sidebar to add the widget to.
 * @param   string  $name       The database name of the widget.
 * @param   mixed   $args       The widget arguments (optional).
 */
function wpse_sws_dev_pre_set_widget( $sidebar, $name, $args = array() ) {
    if ( ! $sidebars = get_option( 'sidebars_widgets' ) )
        $sidebars = array();

    // Create the sidebar if it doesn't exist.
    if ( ! isset( $sidebars[ $sidebar ] ) )
        $sidebars[ $sidebar ] = array();

    // Check for existing saved widgets.
    if ( $widget_opts = get_option( "widget_$name" ) ) {
        // Get next insert id.
        ksort( $widget_opts );
        end( $widget_opts );
        $insert_id = key( $widget_opts );
    } else {
        // None existing, start fresh.
        $widget_opts = array( '_multiwidget' => 1 );
        $insert_id = 0;
    }

    // Add our settings to the stack.
    $widget_opts[ ++$insert_id ] = $args;
    // Add our widget!
    $sidebars[ $sidebar ][] = "$name-$insert_id";

    update_option( 'sidebars_widgets', $sidebars );
    update_option( "widget_$name", $widget_opts );
}





?>
