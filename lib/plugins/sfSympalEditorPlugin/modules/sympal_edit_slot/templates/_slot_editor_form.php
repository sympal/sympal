<?php include_partial('sympal_edit_slot/slot_messages'); ?>

<?php if ($contentSlot->getIsColumn()): ?>
  <?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot') && !isset($form[$contentSlot->getName()])): ?>
    <?php echo $form[$sf_user->getEditCulture()][$contentSlot->getName()] ?>
  <?php else: ?>
    <?php echo $form[$contentSlot->getName()] ?>
  <?php endif; ?>
<?php else: ?>
  <?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot')): ?>
    <?php echo $form[$sf_user->getEditCulture()]['value'] ?>
  <?php else: ?>
    <?php echo $form['value'] ?>
  <?php endif; ?>
<?php endif; ?>

<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery('#sympal_slot_editor_<?php echo $contentSlot->id ?> form').ajaxForm({
      target: '#sympal_slot_editor_<?php echo $contentSlot->id ?> .form_body',
      beforeSubmit: sympalPreSlotSubmit
    });
  });
</script>

<script type="text/javascript">
<?php if (sfSympalConfig::get('elastic_textareas', null, true)) :?>
$(function() {
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?>_editor_form textarea').elastic();
});
<?php endif; ?>
</script>