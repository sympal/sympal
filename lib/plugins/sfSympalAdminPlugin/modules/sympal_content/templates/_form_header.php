<?php if (!$form->isNew()): ?>
  <?php $menuItem = $sf_sympal_content->getMenuItem() ?>
  <div id="toggle_menu_tab">
    <?php echo link_to(($menuItem && $menuItem->exists()) ? 'Edit Menu Item' : 'Add to Menu', '@sympal_content_menu_item?id='.$sf_sympal_content->getId()) ?>
  </div>
<?php endif; ?>