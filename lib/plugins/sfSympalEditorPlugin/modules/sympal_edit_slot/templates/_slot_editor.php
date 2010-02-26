<?php sympal_use_jquery() ?>
<?php use_helper('SympalContentSlotEditor') ?>

<?php include_javascripts_for_form($form) ?>
<?php include_stylesheets_for_form($form) ?>

<div class="sympal_slot_editor" id="sympal_slot_editor_<?php echo $contentSlot->id ?>">
  <?php echo get_sympal_slot_form_tag($form, $contentSlot) ?>
    <?php echo $form->renderHiddenFields() ?>
    
    <div class="form_body">
      <?php echo sfSympalToolkit::getSymfonyResource($contentSlot->getSlotEditFormRenderer(), array('contentSlot' => $contentSlot, 'form' => $form)) ?>
    </div>
    
    <input type="submit" value="save" />
  </form>
</div>

