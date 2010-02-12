<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Menus' => '@sympal_menu_items',
    'Create New Menu Item' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Menus' => '@sympal_menu_items',
    sprintf('Editing Menu Item "%s"', $form->getObject()->getLabel()) => null
  )) ?>
<?php endif; ?>