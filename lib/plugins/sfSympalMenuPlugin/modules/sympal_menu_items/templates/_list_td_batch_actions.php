<td>
  <input type="checkbox" name="ids[]" value="<?php echo $menu_item->getPrimaryKey() ?>" class="sf_admin_batch_checkbox" />
  <input type="hidden" id="select_node-<?php echo $menu_item->getPrimaryKey() ?>" name="new_parent[<?php echo $menu_item->getPrimaryKey() ?>]" />
</td>
