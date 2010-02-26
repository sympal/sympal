<?php if ($contentSlot->getIsColumn()): ?>
  <?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot') && !isset($form[$contentSlot->getName()])): ?>
    <?php echo $form[$sf_user->getEditCulture()][$contentSlot->getName()] ?>
  <?php else: ?>
    <?php echo $form[$contentSlot->getName()] ?>
  <?php endif; ?>
<?php else: ?>
  <?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot')): ?>
    <?php echo $form[$sf_user->getEditCulture()]['value'] ?>
  <?php else: ?>
    <?php echo $form['value'] ?>
  <?php endif; ?>
<?php endif; ?>