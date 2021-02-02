<?php

$fieldArr=array("r_month"=>"Month","r_day"=>"Day","r_month2"=>"Month","r_day2"=>"Day","r_src"=>"Source Book"); 

$file= plugins_url( '/data/data.csv', __FILE__);
//echo $file;

$fp = fopen($file, 'r');
if ($fp) {
	$csvArray = array();
	
	while ($row = fgetcsv($fp)) {
		$csvArray[] = $row;
	}
	
	fclose($fp);
	
	foreach ($csvArray as $tmp) {
		
		// first check to see if it exists
		if (!( sws_ck_post_exists($tmp[2],'dev_reading') )) {
		
			$arr=array("post_author"=>1,"post_content"=>utf8_encode($tmp[4]),"post_title"=>$tmp[2],"post_status"=>"publish","post_type"=>"dev_reading");
			//print_r($arr);
			$id=wp_insert_post($arr, $wp_error=true);
			
			// get id of source book
			$source=query_posts( array(
			'post_type' => 'dev_book',
			's' => $tmp[3],
			'posts_per_page' => 1
			)); 
			//print_r($source); 
			$src_id=$source[0]->ID; 
			
			
			// set meta fields
			add_post_meta($id,'r_month','0');
			add_post_meta($id,'r_day','0');
			add_post_meta($id,'r_month2','0');
			add_post_meta($id,'r_day2','0');
			add_post_meta($id,'r_src',$src_id);
			add_post_meta($id,'r_script','YES');
			add_post_meta($id,'r_used','0');
		} else { echo "Post already exists."; }
	}
} else { echo "COULD NOT OPEN FILE!"; }
?>
