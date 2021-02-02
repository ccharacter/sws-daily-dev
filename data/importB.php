<?php

$fieldArr2=array("b_auth_fname"=>"Author Firstname","b_auth_lname"=>"Author Lastname","b_publisher"=>"Publisher","b_copyright"=>"Copyright","b_link"=>"Link to Purchase");

$file= plugins_url( '/data/dataB.csv', __FILE__ );

$fp = fopen($file, 'r');
if ($fp) {
	$csvArray = array();
	
	while ($row = fgetcsv($fp)) {
		$csvArray[] = $row;
	}
	
	fclose($fp);
	
	foreach ($csvArray as $tmp) {

		// first check to see if it exists
		if (!( sws_ck_post_exists($tmp[3],'dev_book') )) {
		
			$arr=array("post_author"=>1,"post_content"=>utf8_encode($tmp[2]),"post_title"=>$tmp[3],"post_status"=>"publish","post_type"=>"dev_book");
			$id=wp_insert_post($arr, $wp_error=true);
			
			// set meta fields
			add_post_meta($id,'b_auth_fname',$tmp[5]);
			add_post_meta($id,'b_auth_lname',$tmp[6]);
			add_post_meta($id,'b_publisher',$tmp[7]);
			add_post_meta($id,'b_copyright',$tmp[8]);
			add_post_meta($id,'b_link',$tmp[9]);
			
			// THUMBNAIL	
			$image_url = plugins_url( 'img/'.$tmp[1], __FILE__); //echo $image_url;
			
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
		
		} else { echo "Post already exists."; }
	} // foreach

} else { echo "COULD NOT OPEN FILE!"; }
?>
