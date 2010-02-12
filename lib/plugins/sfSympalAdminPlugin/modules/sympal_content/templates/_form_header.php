<?php $contentType = $form->getObject()->getType() ?>

<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Site Content' => '@sympal_content_types_index',
    $contentType->getLabel() => '@sympal_content_list_type?type='.$contentType->getSlug(),
    'Create New' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Site Content' => '@sympal_content_types_index',
    $contentType->getLabel() => '@sympal_content_list_type?type='.$contentType->getSlug(),
    'Editing '.$sf_sympal_content->getTitle() => $sf_sympal_content->getEditRoute()
  )) ?>
<?php endif; ?>