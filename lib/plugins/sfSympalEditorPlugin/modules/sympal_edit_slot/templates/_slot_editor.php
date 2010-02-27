<?php sympal_use_jquery() ?>
<?php use_helper('SympalContentSlotEditor') ?>

<?php include_javascripts_for_form($form) ?>
<?php include_stylesheets_for_form($form) ?>

<?php echo get_sympal_slot_form_tag($form, $contentSlot, $options['edit_mode']) ?>
  <?php echo $form->renderHiddenFields() ?>
  
  <div class="form_body">
    <?php echo sfSympalToolkit::getSymfonyResource($contentSlot->getSlotEditFormRenderer(), array('contentSlot' => $contentSlot, 'form' => $form)) ?>
  </div>
  
  <input type="button" value="cancel" class="button cancel" />
  <input type="submit" value="save" class="button" />
  <?php echo image_tag('/sfSympalPlugin/images/loading_circle.gif', array('alt' => 'loading...', 'class' => 'loading_anim')) ?>
</form>