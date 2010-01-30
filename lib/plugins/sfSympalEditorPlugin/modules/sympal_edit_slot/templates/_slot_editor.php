<?php sympal_use_jquery() ?>
<?php sympal_use_javascript('/sfSympalPlugin/js/jQuery.form.js') ?>
<?php sympal_use_javascript('/sfSympalEditorPlugin/js/editor.js') ?>

<div class="sympal_content_slot_editor sympal_form">
  <form>
    <input type="hidden" name="content_ids[<?php echo $contentSlot->getId() ?>]" value="<?php echo $contentSlot->getContentRenderedFor()->getId() ?>" />
    <?php if (!$contentSlot->is_column): ?>
      <a class="sympal_change_slot_type" onClick="return false;">>> <?php echo __('Change Slot Type') ?></a>
      <div class="sympal_change_slot_type_dropdown">
        <?php echo $form['type'] ?>
      </div>
    <?php endif; ?>

    <span class="sympal_content_slot_editor_form">
      <?php echo get_partial('sympal_edit_slot/slot_editor_form', array('form' => $form, 'contentSlot' => $contentSlot)) ?>
    </span>
  </form>
</div>