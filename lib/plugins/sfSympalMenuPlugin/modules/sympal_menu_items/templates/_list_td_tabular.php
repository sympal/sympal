<td style="white-space: nowrap;" class="sf_admin_text sf_admin_list_td_label">
  <span class="<?php echo $menu_item->getNode()->isLeaf() ? 'file' : 'folder' ?>">
    <?php echo link_to($menu_item['label'], 'sympal_menu_items_edit', $menu_item) ?>
    <?php if (!$menu_item->getNode()->isLeaf() && $menu_item->getLevel() == 0): ?>
      <small>(<?php echo $menu_item['name'] ?>)</small>
    <?php endif; ?>
  </span>
</td>