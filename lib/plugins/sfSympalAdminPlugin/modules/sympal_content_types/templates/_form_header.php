<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Content Types' => '@sympal_content_types',
    'Create New Content Type' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Content Types' => '@sympal_content_types',
    sprintf('Editing Content Type "%s"', $form->getObject()->getLabel()) => null
  )) ?>
<?php endif; ?>