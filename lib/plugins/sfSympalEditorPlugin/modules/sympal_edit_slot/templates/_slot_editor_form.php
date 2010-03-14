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
    jQuery('#sympal_slot_wrapper_<?php echo $contentSlot->id ?> form').submit(function(){
      sympal_slot_form_submit(jQuery(this))
      
      return false;
    });
  });
</script>

<script type="text/javascript">
<?php if (sfSympalConfig::get('elastic_textareas', null, true)) :?>
$(function() {
  $('#sympal_slot_wrapper_<?php echo $contentSlot->getId() ?> form textarea').elastic();
});
<?php endif; ?>
</script>