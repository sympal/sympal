<?php if ($form->isNew()): ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Site Content' => '@sympal_content_types_index',
    'Slots' => '@sympal_content_slots',
    'Create New Slot' => null
  )) ?>
<?php else: ?>
  <?php echo get_sympal_breadcrumbs(array(
    'Dashboard' => '@sympal_dashboard',
    'Site Content' => '@sympal_content_types_index',
    'Slots' => '@sympal_content_slots',
    sprintf(__('Editing Slot "%s"'), $sf_sympal_content_slot->getName()) => null
  )) ?>
<?php endif; ?>
