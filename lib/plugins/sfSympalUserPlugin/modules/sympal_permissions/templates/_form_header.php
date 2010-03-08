<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Permissions' => '@sympal_permissions',
    'Create New Permission' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Permissions' => '@sympal_permissions',
    sprintf(__('Editing Permission "%s"'), $form->getObject()->getName()) => null
  )) ?>
<?php endif; ?>
