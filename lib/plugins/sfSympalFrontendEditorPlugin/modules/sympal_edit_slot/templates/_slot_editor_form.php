<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<span class="sympal_<?php echo sfInflector::tableize($contentSlot->getType()) ?>_content_slot_editor_form">
  <?php echo $form->renderHiddenFields() ?>

  <?php if ($contentSlot->getIsColumn()): ?>
    <?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot') && !isset($form[$contentSlot->getName()])): ?>
      <?php echo $form[$sf_user->getCulture()][$contentSlot->getName()] ?>
    <?php else: ?>
      <?php echo $form[$contentSlot->getName()] ?>
    <?php endif; ?>
  <?php else: ?>
    <?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot')): ?>
      <?php echo $form[$sf_user->getCulture()]['value'] ?>
    <?php else: ?>
      <?php echo $form['value'] ?>
    <?php endif; ?>
  <?php endif; ?>
</span>

<script type="text/javascript">
$('.sympal_markdown_content_slot_editor_form textarea').markItUp(mySettings);
$('.sympal_raw_html_content_slot_editor_form textarea, .sympal_markdown_content_slot_editor_form textarea').elastic();
</script>