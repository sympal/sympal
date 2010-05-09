<?php
/*
 * Renders a form for a content slot
 * 
 * @see get_sympal_content_slot_editor()
 */
?>

<?php sympal_use_jquery() ?>
<?php use_helper('SympalContentSlotEditor') ?>

<?php include_javascripts_for_form($form) ?>
<?php include_stylesheets_for_form($form) ?>

<?php
  $typeJson = htmlentities(json_encode(array(
    'url' => url_for('@sympal_change_content_slot_type?content_id='.$contentSlot->getContentRenderedFor()->id.'&id='.$contentSlot->id.'&new_type=replace')
  )));
?>

<?php echo get_sympal_slot_form_tag($form, $contentSlot) ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form->renderGlobalErrors() ?>

  <?php if (!$contentSlot->is_column): ?>
    <a href="#" class="sympal_change_slot_type" onClick="return false;"><?php echo __('Change Slot Type') ?></a>
    <div class="sympal_change_slot_type_dropdown <?php echo $typeJson ?>">
      <?php echo $form['type'] ?>
    </div>
  <?php endif; ?>
  
  <div class="form_body">
    <?php echo sfSympalToolkit::getSymfonyResource($contentSlot->getSlotEditFormRenderer(), array('contentSlot' => $contentSlot, 'form' => $form)) ?>
  </div>
  
  <input type="submit" value="<?php echo __('Save'); ?>" class="button" />
  <input type="button" value="<?php echo __('Cancel'); ?>" class="button cancel" />
</form>
