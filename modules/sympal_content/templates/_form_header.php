<?php $menuItem = $form->getObject()->getMenuItem() ?>
<div id="toggle_menu_tab">
  <?php echo link_to(($menuItem && $menuItem->exists()) ? 'Edit Menu Item' : 'Add to Menu', '@sympal_content_menu_item?id='.$form->getObject()->getId()) ?>
</div>