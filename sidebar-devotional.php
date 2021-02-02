<div class="shift-right--fluid bg--beige can-be--dark-dark sws-sidebar">
  <?php if (is_active_sidebar('sidebar_breakout_block')): ?>
    <div class="<?php if (!is_page_template('template-news.php')): echo 'block--breakout '; endif; ?>media-block block spacing bg--tan can-be--dark-dark pad--secondary--for-breakouts">
    <?php if ($breakout===false) { dynamic_sidebar('sidebar_breakout_block'); }  ?>
    </div>
  <?php endif; ?>

  <div class="column__secondary can-be--dark-dark sws-sidebar">
    <aside class="aside">
      <div class="text pad--secondary">
 	   <?php if ($sidebar===false) { dynamic_sidebar('sidebar'); } else { echo $sidebar; } ?>
		</div>
    </aside>
  </div>
</div> <!-- /.shift-right--fluid -->
