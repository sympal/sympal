<?php use_helper('jQuery') ?>
<?php use_javascript('/sfSympalPlugin/js/jQuery.form.js') ?>
<?php use_javascript('/sfSympalPlugin/js/editor.js') ?>

<div class="sympal_content_slot_editor sympal_<?php echo sfInflector::tableize($contentSlot->getType()->getName()) ?>_content_slot_editor sympal_form">
  <?php echo jq_form_remote_tag(array(
    'url' => url_for('@sympal_save_content_slot?content_id='.$contentSlot->getContentRenderedFor()->getId().'&id='.$contentSlot->getId()),
    'update' => "#sympal_content_slot_".$contentSlot->getId()." .value"
  )) ?>
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
  </form>
</div>