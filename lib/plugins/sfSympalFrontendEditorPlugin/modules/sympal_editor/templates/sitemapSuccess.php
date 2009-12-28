<h2><?php echo __('Sitemap') ?></h2>

<?php foreach ($roots as $root): ?>
  <h3><?php echo ucfirst($root['name']) ?> Menu</h3>
  <?php
  $menu = get_sympal_menu($root['name'], true);
  echo $menu;
  ?>
<?php endforeach; ?>