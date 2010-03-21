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

<?php echo get_sympal_slot_form_tag($form, $contentSlot) ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php echo $form->renderGlobalErrors() ?>
  
  <div class="form_body">
    <?php echo sfSympalToolkit::getSymfonyResource($contentSlot->getSlotEditFormRenderer(), array('contentSlot' => $contentSlot, 'form' => $form)) ?>
  </div>
  
  <input type="submit" value="<?php echo __('Save'); ?>" class="button" />
  <input type="button" value="<?php echo __('Cancel'); ?>" class="button cancel" />
</form>
