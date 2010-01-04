<td>
  <input type="checkbox" name="ids[]" value="[?php echo $<?php echo $this->getSingularName() ?>->getPrimaryKey() ?]" class="sf_admin_batch_checkbox" />
  <?php if (Doctrine_Core::getTable($this->getModelClass())->hasTemplate('Doctrine_Template_NestedSet')): ?>
    <input type="hidden" id="select_node-[?php echo $<?php echo $this->getSingularName() ?>->getPrimaryKey() ?]" name="new_parent[[?php echo $<?php echo $this->getSingularName() ?>->getPrimaryKey() ?]]" />
  <?php endif; ?>
</td>