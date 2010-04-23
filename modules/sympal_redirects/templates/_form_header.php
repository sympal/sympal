<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    '404 Redirects' => '@sympal_redirects',
    'Create New Redirect' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    '404 Redirects' => '@sympal_redirects',
    'Editing 404 Redirect' => null
  )) ?>
<?php endif; ?>