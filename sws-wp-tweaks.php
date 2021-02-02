<?php
/** This is a test comment to test stuff that requires testing ~ Beth */

/**
 * Plugin Name:       SWS WordPress Tweaks
 * Plugin URI:        https://ccharacter.com/custom-plugins/sws-wp-tweaks/
 * Description:       Various tweaks that I'll want on most or all of my WordPress sites
 * Version:           6.0
 * Requires at least: 5.2
 * Requires PHP:      5.5
 * Author:            Sharon Stromberg
 * Author URI:        https://ccharacter.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sws-wp-tweaks
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once plugin_dir_path(__FILE__).'inc/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://raw.githubusercontent.com/ccharacter/sws-wp-tweaks/main/plugin.json',
	__FILE__,
	'sws_wp_tweaks'
);


$optVals = get_option( 'sws_wp_tweaks_options' );

require_once plugin_dir_path(__FILE__).'func_duplicate.php';
require_once plugin_dir_path(__FILE__).'func_options.php';
require_once plugin_dir_path(__FILE__).'func_spreadsheet.php';
require_once plugin_dir_path(__FILE__).'func_login.php';
require_once plugin_dir_path(__FILE__).'func_post_counter.php';
require_once plugin_dir_path(__FILE__).'func_cron.php';
require_once plugin_dir_path(__FILE__).'func_shortcodes.php';

// add stylesheets
function sws_wp_tweaks_enqueue_script() {   
 	wp_enqueue_style( 'swsTweakStyles', plugin_dir_url(__FILE__).'inc/sws_tweaks_style.css');
}
add_action('wp_enqueue_scripts', 'sws_wp_tweaks_enqueue_script');


// FOR NON-MULTI-SITES
/*@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );*/

/* FOR MULTI-SITES, change the following in /etc/php.ini:

upload_max_filesize = 1500M
post_max_size = 1500M
memory_limit = 1500M

... then sudo systemctl restart php-fpm
*/

// ADD FEATURE TO REDIRECT LIST OF THINGS LIKE /wp-content/, /blogs/, /portal/, etc.
// ADD JAVASCRIPT STUFF FROM VERSACARE

// OFF BY DEFAULT - DISABLED UNTIL I CAN TROUBLESHOOT THIS FUNC
/*if (($optVals['screen_urls']=="on")) {
	function sws_tweaks_screen_url() {
		$terms="https://docs.google.com/spreadsheets/d/e/2PACX-1vR3Yr5nkluQS8q87oUMx9-8jHgfli67zJvU4TUgfuWDAPZSxPpK8N7tPOLIKde8S3-5fBntmTivxnO_/pub?output=csv";
		
		$reqURI=$_SERVER['REQUEST_URI'];
		//error_log($reqURI,0);
		if (is_string($reqURI)) {
			$banArr=sws_tweaks_csvToArray($terms,',',"N");
			foreach ($banArr as $term) { 
				$term=strval($term);
					if (strlen($term)>0) {
					if (!(strpos($reqURI, $term)===false))  {
						wp_redirect( home_url()."/bad-url" );
						die;
					}
				}
			} 
		} // has $reqURI
	 }
	 add_action( 'template_redirect', 'sws_tweaks_screen_url' );
}*/

// ON BY DEFAULT
if ((!(isset($optVals['hide_author']))) || ($optVals['hide_author']=="on")) {
	function sws_tweaks_hide_author_page() {
		 if (false !== strpos($_SERVER['REQUEST_URI'], '?'))  {
			 if (!(empty($_GET['author']))) {
				 wp_redirect( home_url()."/bad-author" );
				 die;
			}
		 } else { return; }
	 }
	 add_action( 'template_redirect', 'sws_tweaks_hide_author_page' );
}

// ON BY DEFAULT
if ((!(isset($optVals['fix_its_fontpath']))) || ($optVals['fix_its_fontpath']=="on")) {
	// FIX BUG IN iThemes Security path
	if ( ! function_exists( 'it_icon_font_admin_enueue_scripts' ) ) {
			function it_icon_font_admin_enueue_scripts() {
					$url=plugins_url();

					if ( version_compare( $GLOBALS['wp_version'], '3.7.10', '>=' ) ) {
							$dir = str_replace( '\\', '/', dirname( __FILE__ ) );

							$content_dir = rtrim( str_replace( '\\', '/', WP_CONTENT_DIR ), '/' );
							$abspath = rtrim( str_replace( '\\', '/', ABSPATH ), '/' );

							if ( empty( $content_dir ) || ( 0 === strpos( $dir, $content_dir ) ) ) {
									$url = WP_CONTENT_URL . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $content_dir, '/' ) . '/', '', $dir ) );
							} else if ( empty( $abspath ) || ( 0 === strpos( $dir, $abspath ) ) ) {
									$url = get_option( 'siteurl' ) . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $abspath, '/' ) . '/', '', $dir ) );
							}

							if ( empty( $url ) ) {
									$dir = realpath( $dir );

									if ( empty( $content_dir ) || ( 0 === strpos( $dir, $content_dir ) ) ) {
											$url = WP_CONTENT_URL . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $content_dir, '/' ) . '/', '', $dir ) );
									} else if ( empty( $abspath ) || ( 0 === strpos( $dir, $abspath ) ) ) {
											$url = get_option( 'siteurl' ) . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $abspath, '/' ) . '/', '', $dir ) );
									}
							}

							if ( is_ssl() ) {
									$url = preg_replace( '|^http://|', 'https://', $url );
							} else {
									$url = preg_replace( '|^https://|', 'http://', $url );
							}


							wp_enqueue_style( 'ithemes-icon-font', "$url/better-wp-security/lib/icon-fonts/icon-fonts.css" );
					}
			}
			add_action( 'admin_enqueue_scripts', 'it_icon_font_admin_enueue_scripts' );
	}
	//error_log("One",0);
}


// ON BY DEFAULT
if ((!(isset($optVals['fix_pw_reset_msg']))) || ($optVals['fix_pw_reset_msg']=="on")) {
	
	function sws_set_content_type(){
		return "text/html";
	}
	add_filter( 'wp_mail_content_type','sws_set_content_type' );

	
	// FIX  BUG IN PASSWORD RESET MSG
	add_filter("retrieve_password_message", "sws_custom_password_reset", 99, 4);

	function sws_custom_password_reset($message, $key, $user_login, $user_data )    {
		$message = "<p>Someone has requested a password reset for the following account:<br />" . sprintf(__('%s'), $user_data->user_email) . "<br />";
		$message .= "If this was a mistake, just ignore this email and nothing will happen.</p>";
		$message .= __( "<p>To set your password, <a href='", 'personalize-login' ) ;
		$message .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );
		$message .= __( "'>CLICK HERE</a> or visit the following address:<br />", 'personalize-login' );
		$message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "</p><p>~The Friendly Script</p>";

		return $message;
	}
	//error_log("Two",0);
}

// ON BY DEFAULT
if ((!(isset($optVals['disable_newUser_notice']))) || ($optVals['disable_newUser_notice']=="on")) {
	// DISABLE ADMIN default WordPress new user notification emails
	if ( ! function_exists ( 'wp_new_user_notification' ) ) :
		function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {

			global $wpdb, $wp_hasher;
			$user = get_userdata( $user_id );

			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );

			/** This action is documented in wp-login.php */
			do_action( 'retrieve_password_key', $user->user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

			$switched_locale = switch_to_locale( get_user_locale( $user ) );

			$message = "<p>Thank you for registering on $blogname.</p><p>".sprintf(__('Your username is: %s'), $user->user_login);
			$message .= __( "</p><p>To set your password, <a href='", 'personalize-login' ) ;
			$message .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' );
			$message .= __( "'>CLICK HERE</a> or visit the following address:", 'personalize-login' ) . "</p>";
			$message.= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . "<br /><br />~The Friendly Script";

			wp_mail($user->user_email, sprintf(__('[%s]: Your username and password info'), $blogname), $message);
		}
	endif;
}

// ON BY DEFAULT
if ((!(isset($optVals['disable_xmlrpc']))) || ($optVals['disable_xmlrpc']=="on")) {
	// Disable use XML-RPC
	add_filter( 'xmlrpc_enabled', '__return_false' );
}

// OFF BY DEFAULT
if ((isset($optVals['screen_grav_forms'])) && ($optVals['screen_grav_forms']=="on")) {
	// GRAVITY FORMS VALIDATION: FAIL HTML & FOREIGN CHARS
	if (isset($optVals['screen_form_ids'])) { 
		$idArr=explode(",",$optVals['screen_form_ids']);
	}	
	//error_log(print_r($idArr,true),0);

	foreach ($idArr as $formID) {
		if (strlen(trim($formID))>0) {
			$formID=intval(trim ($formID));
			
			add_filter( 'gform_validation_'.$formID, 'sws_custom_validation' );
			//error_log('gform_validation_'.$formID,0);
		}
	}
}


// OFF BY DEFAULT 
if ((isset($optVals['email_banning'])) && ($optVals['email_banning']=="on")) {
	// USE A LIST OF KEYWORDS AND EXTENSIONS TO BLOCK SPAMMISH REGISTRATIONS
	add_filter( 'registration_errors', 'sws_tweaks_email_banning', 10, 3 );
}

// ON BY DEFAULT
if ((!(isset($optVals['show_server_name']))) || ($optVals['show_server_name']=="on")) {
	$shortcode=new DisplayServerName();
	$shortcode->init();
}

// OFF BY DEFAULT 
if ((isset($optVals['maintenance_mode'])) && ($optVals['maintenance_mode']=="on")) {
	function sws_maintenance_mode() {
		if (!current_user_can('edit_themes') || !is_user_logged_in()) {
			wp_die('<h1>NOT AVAILABLE</h1><br />This site is not presently available. Check with an administrator.');
		}
	}
	add_action('get_header', 'sws_maintenance_mode');
}

function sws_test_input($value) {
	//error_log($value,0);
	// TEST RUSSIAN: Это образец символов на запрещенном языке.
	// TEST CHINESE: 这是一种禁止语言字符的示例。
	$test=""; $ret=true;
	$testHTML=strip_tags($value,"<p><br><span><em><strong><li><ul>");	
	$testHTML=str_replace("http","",$testHTML); // filter out web addresses
	$testHTML=str_replace("www","",$testHTML); // filter out web addresses
	
	$testLang=preg_replace('/[^\00-\255]+/u', '', $value);
	if ($value!=$testHTML) { 
		$test=" HTML is not allowed in this field."; 
		//error_log("HTML",0); 
	}
	if ($value!=$testLang) { 
		$test= "Non-English characters are not allowed in this field."; 
		//error_log("Non-English",0);
	}
		
	if (!($test=="")) { $ret=false;}
	$retArr=array($ret,$test);
	return $retArr;
}

function sws_custom_validation( $validation_result ) {
	$form = $validation_result['form'];
	foreach( $form['fields'] as &$field ) {
		if (!($field->failed_validation)) {
			if (is_array($field->inputs)) { 
				$value = $field->inputs;
			} else {  
				$value=rgpost("input_".$field->id, true);
			}
			if (is_array($value)) { 
				$k=1;
				foreach ($value as $item) {
					$itemText=rgpost("input_".$field->id."_".$k);
					$test=sws_test_input($itemText);
					if (!$test[0]) break;
					$k++;
				}
			} else { 
				$test=sws_test_input($value);
			}

			if ( !$test[0] ) {
				// set the form validation to false
				$validation_result['is_valid'] = false;

				$field->failed_validation = true;
				$field->validation_message = $field->label.' is invalid. '.$test[1];
				return $validation_result;
			}
		} 
	}
	//Assign modified $form object back to the validation result
	$validation_result['form'] = $form;
	return $validation_result;
}
	

function sws_console_log($output, $with_script_tags = true) {
	if (is_array($output)) { $output=var_dump($output); }
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . 
');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}

class DisplayServerName
{
	public function showTag($content) {
		if ( (is_page('home')) || (is_page('about'))) {
			return $content.'<span style="opacity:0.02">'.gethostname().'</span>';
		} else { 
			return $content;
		}
	}
	
    public function register($atts, $content = null)
    {
        return '<span style="opacity:0.02">'.gethostname().'</span>';
    }
    
	public function init()
    {
        add_shortcode('sws_server_tag', array($this, 'register'));
		add_action('the_content',array($this,'showTag'));
    }
}



// SHORTCODE FOR COLLAPSING DIVS  
function sws_accordion_func($atts) {
	$a=shortcode_atts(array(
	  'item_id' => 'content',
	  'test' => 'foobar'
	), $atts);
	$itemID=$a['item_id']; // NOTE TO SELF: SHORTCODE_ATTS DOESN'T LIKE UPPERCASE!!!!
	ob_start();
	
?>
<script>
if ('#<?php echo $itemID; ?>').length()) {
	jQuery('#<?php echo $itemID; ?>').prepend("<div class='sws-magic-div'>");
	jQuery('#<?php echo $itemID; ?>').append("</div>");
				
	jQuery(".sws-magic-div > ul").each(function (index, element) {
		if ( jQuery( element ).children().length > 0) {
			jQuery(element).wrap("<div class='sws-accordion'></div>");
			jQuery(element).before( "<input type='checkbox' id='toggle" + index + "' class='sws-acc-ck' /><label for='toggle" + index + "'>&nbsp;&nbsp;&nbsp;</label>" );	
			jQuery(element).wrap("<div class='sws-acc-content'></div>");
			console.log('index' + index);
		}
		});
}		
</script> <?php
	ob_end_clean();
}

// register shortcode
add_shortcode('sws_accordion', 'sws_accordion_func'); 

// FIX SVG CROPPING ERROR
add_action( 'after_setup_theme', 'sws_bodhi_svg_theme_support', 11 );
function sws_bodhi_svg_theme_support() {

	remove_theme_support( 'custom-logo' );

	add_theme_support( 'custom-logo', array(
		'flex-width'  => true,
		'flex-height' => true,
	) );

}



?>
