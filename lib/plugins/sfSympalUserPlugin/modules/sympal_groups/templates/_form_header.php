<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Groups' => '@sympal_groups',
    'Create New Group' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Groups' => '@sympal_groups',
    sprintf('Editing Group "%s"', $form->getObject()->getName()) => null
  )) ?>
<?php endif; ?>