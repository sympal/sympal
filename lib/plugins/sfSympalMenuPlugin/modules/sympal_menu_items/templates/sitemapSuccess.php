<?php echo get_sympal_breadcrumbs($menuItem, null, null, true) ?>

<h2>Sitemap</h2>

<?php foreach ($roots as $root): ?>
  <h3><?php echo ucfirst($root['name']) ?> Menu</h3>
  <?php
  $menu = get_sympal_menu($root['name'], true);
  $menu->showMenuItemDropDown(false);
  echo $menu;
  ?>
<?php endforeach; ?>