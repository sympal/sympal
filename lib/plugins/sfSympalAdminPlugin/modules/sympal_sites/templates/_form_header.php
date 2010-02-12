<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Sites' => '@sympal_sites',
    'Create New Site' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Sites' => '@sympal_sites',
    sprintf('Editing Site "%s"', $form->getObject()->getTitle()) => null
  )) ?>
<?php endif; ?>