<?php foreach ($this->configuration->getValue('list.display') as $name => $field): ?>
<?php if ($field->isLink() && $this->isNestedSet()): ?>
<?php echo $this->addCredentialCondition(sprintf(<<<EOF
<td class="sf_admin_%s sf_admin_list_td_%s">
  [?php echo %s ?][?php echo %s ?]
</td>

EOF
, strtolower($field->getType()), $name, $this->getNestedSetIndention(), $this->renderField($field)), $field->getConfig()) ?>
<?php else: ?>
<?php echo $this->addCredentialCondition(sprintf(<<<EOF
<td class="sf_admin_%s sf_admin_list_td_%s">
  [?php echo %s ?]
</td>

EOF
, strtolower($field->getType()), $name, $this->renderField($field)), $field->getConfig()) ?>
<?php endif; ?>
<?php endforeach; ?>