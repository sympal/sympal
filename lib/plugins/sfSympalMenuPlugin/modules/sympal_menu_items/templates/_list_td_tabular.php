<td class="sf_admin_text sf_admin_list_td_name">
  <span class="<?php echo $sf_sympal_menu_item->getNode()->isLeaf() ? 'file' : 'folder' ?>">
    <?php echo link_to($sf_sympal_menu_item->getName(), 'sympal_menu_items_edit', $sf_sympal_menu_item) ?>
  </span>
</td>
<td class="sf_admin_text sf_admin_list_td_is_published">
  <?php echo get_partial('sympal_menu_items/is_published', array('type' => 'list', 'sf_sympal_menu_item' => $sf_sympal_menu_item)) ?>
</td>
