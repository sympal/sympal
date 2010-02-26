<?php sympal_use_jquery() ?>
<?php sympal_use_javascript('/sfSympalPlugin/js/jQuery.form.js') ?>
<?php sympal_use_javascript('/sfSympalEditorPlugin/js/editor.js') ?>

<?php include_javascripts_for_form($form) ?>
<?php include_stylesheets_for_form($form) ?>

<div class="sympal_slot_editor" id="sympal_slot_editor_<?php echo $contentSlot->id ?>">
  <?php echo $form->renderFormTag(url_for('sympal_save_content_slot', $contentSlot), array('method' => 'put')) ?>
    <?php echo $form->renderHiddenFields() ?>
    <?php echo sfSympalToolkit::getSymfonyResource($contentSlot->getSlotEditFormRenderer(), array('contentSlot' => $contentSlot, 'form' => $form)) ?>
  </form>
</div>


<script type="text/javascript">
<?php if (sfSympalConfig::get('elastic_textareas', null, true)) :?>
$(function() {
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?>_editor_form textarea').elastic();
});
<?php endif; ?>
</script>
