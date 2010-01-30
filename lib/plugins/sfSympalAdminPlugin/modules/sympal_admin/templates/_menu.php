<div class="sympal_top_bar_background"></div>

<div class="sympal_signout_icon">
  <?php echo link_to(image_tag('/sfSympalPlugin/images/signout.png', 'title='.__('Signout')), '@sympal_signout', 'confirm='.__('Are you sure you want to signout?')) ?>
</div>

<div class="sympal_admin_menu">
  <?php if ($menu = $menu->render()): ?>
    <div id="sympal_admin_menu">
      <?php echo $menu ?>
    </div>
  <?php endif; ?>
</div>