<?php $menu->setUlClass('menu expandfirst') ?>

<?php if ($menu = $menu->render()): ?>
  <?php use_helper('jQuery') ?>
  <?php use_javascript('/sfSympalPlugin/js/jQuery.cookie.js') ?>
  <?php use_javascript('/sfSympalPlugin/js/jQuery.menu.js') ?>

  <div id="sympal_admin_menu">
    <?php echo $menu ?>
  </div>
<?php endif; ?>