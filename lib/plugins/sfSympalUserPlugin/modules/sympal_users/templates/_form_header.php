<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Users' => '@sympal_users',
    'Create New User' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Users' => '@sympal_users',
    sprintf(__('Editing User "%s"'), $form->getObject()->getUsername()) => null
  )) ?>
<?php endif; ?>
