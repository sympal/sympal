<?php include_partial('sympal_edit_slot/slot_messages'); ?>

<?php if (sfSympalConfig::isI18nEnabled('sfSympalContentSlot')): ?>
  <?php echo $form[$sf_user->getEditCulture()]['value'] ?>
<?php else: ?>
  <?php echo $form['value'] ?>
<?php endif; ?>

<script type="text/javascript">
<?php if (sfSympalConfig::get('elastic_textareas', null, true)) :?>
$(function() {
  $('#sympal_slot_wrapper_<?php echo $contentSlot->getId() ?> form textarea').elastic();
});
<?php endif; ?>
</script>