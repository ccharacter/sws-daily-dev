<?php   
/*
 Template Name: Daily Devotional
 */

//    $timezone = $_SESSION['time'];

if (isset($_GET['id'])) { $t=$_GET['id']; } else { $t=0;}
	
devotional_import_default();

if (get_post_type()=='page') { // it's the daily page
	$devArr=dev_get_daily($t);
}

while (have_posts()) : the_post(); 

$banner = array("book-159737","book-247644","book-261768","book-415078","book-1526049");
$rand_key = array_rand($banner);

?>
  <style type="text/css">
    .header-swath--with-image {
      background-image: url('<?php echo plugin_dir_url(__FILE__).'img/'.$banner[$rand_key].".jpg"; ?>');
    }
    @media (min-width: 800px) {
      .header-swath--with-image {
        background-image: url('<?php echo plugin_dir_url(__FILE__).'img/'.$banner[$rand_key].".jpg"; ?>');
      }
    }
    @media (min-width: 1100px) {
      .header-swath--with-image {
        background-image: url('<?php echo plugin_dir_url(__FILE__).'img/'.$banner[$rand_key].".jpg"; ?>');
      }
    }
  </style>
<header class="header__swath theme--primary-background-color blend-mode--multiply header-swath--with-image">
	<?php dynamic_sidebar( 'single-after-content-widget-area' ); ?>
  <div class="layout-container cf">
    <div class="flex-container cf">
      <div class="shift-left--fluid" style='z-index:99999'>
        <h1 class="font--tertiary--xl white">Devotional Readings
        </h1>
      </div>
      <div class="shift-right--fluid"></div> <!-- /.shift-right--fluid -->
    </div>
  </div>
</header> <!-- /.header__swath -->
<div class="layout-container full--until-large">
  <div class="flex-container cf">
    <div class="shift-left--fluid column__primary bg--white can-be--dark-light no-pad--btm">
      <div class="pad--primary spacing">
        <div class="text article__body spacing"><?php 

			if (get_post_type()=='page') { // it's the daily page
				//print_r($myArr);
				dev_display($devArr[0]);
			} else { 

				echo "<h2>".get_the_title()."</h2>";
		
				the_content();	
		
				show_devotional_link(get_post_meta(get_the_ID(),'r_src',true)); 
				
			}
				?>
      
        </div>
      </div>
      <?php include(locate_template('templates/block-layout.php')); ?>
    </div> <!-- /.shift-left--fluid -->
<div class="shift-right--fluid bg--beige can-be--dark-dark sws-sidebar">
    <div class="<?php if (!is_page_template('template-news.php')): echo 'block--breakout '; endif; ?>media-block block spacing bg--tan can-be--dark-dark pad--secondary--for-breakouts">
    <div class="text article__body spacing">
    <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("Devotional Sidebar (Top)") ) : ?>
<?php endif;?>
    </div>
    </div>
  <div class="column__secondary can-be--dark-dark sws-sidebar">
    <aside class="aside">
      <div class="text pad--secondary">
<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar("Devotional Sidebar (Bottom)") ) : ?>
<?php endif;?>
		</div>
    </aside>
  </div>
</div> <!-- /.shift-right--fluid -->
  </div> <!-- /.flex-container -->
</div> <!-- /.layout-container -->
<?php endwhile; ?>