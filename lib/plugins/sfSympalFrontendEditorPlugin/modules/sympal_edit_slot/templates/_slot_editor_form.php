<?php include_stylesheets_for_form($form) ?>
<?php include_javascripts_for_form($form) ?>

<span id="sympal_content_slot_<?php echo $contentSlot->getId() ?>_editor_form" class="sympal_<?php echo sfInflector::tableize($contentSlot->getType()) ?>_content_slot_editor_form">
  <?php echo $form->renderHiddenFields() ?>

  <?php echo sfSympalToolkit::getSymfonyResource($contentSlot->getSlotEditFormRenderer(), array('contentSlot' => $contentSlot, 'form' => $form)) ?>
</span>

<script type="text/javascript">
<?php if ($contentSlot->getType() == 'Markdown' && sfSympalConfig::get('enable_markdown_editor', null, true)): ?>
$(function() {
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?>_editor_form textarea').markItUp(mySettings);
});
<?php endif; ?>

<?php if (sfSympalConfig::get('elastic_textareas', null, true)) :?>
$(function() {
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?>_editor_form textarea').elastic();
});
<?php endif; ?>
</script>