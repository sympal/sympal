<?php use_helper('jQuery') ?>

<div class="sympal_content_slot_editor sympal_form">
  <form>
    <?php echo $form->renderHiddenFields() ?>

    <?php if ($contentSlot->getIsColumn()): ?>
      <?php echo $form[$contentSlot->getName()] ?>
    <?php else: ?>
      <?php echo $form['value'] ?>
    <?php endif; ?>

    <input type="button" name="cancel" class="cancel" value="Cancel" />

    <?php echo jq_submit_to_remote('save', 'Save', array(
      'url' => url_for('@sympal_save_content_slot?content_id='.$sf_request->getParameter('content_id').'&id='.$sf_request->getParameter('id')),
      'complete' => "$('#sympal_content_slot_".$contentSlot->getId()."').find('.editor').hide(); $('#sympal_content_slot_".$contentSlot->getId()."').find('.value').show()",
      'update' => "#sympal_content_slot_".$contentSlot->getId()." .value"
    )) ?>
  </form>
</div>

<script type="text/javascript">
  $(function()
  {
    $('.sympal_content_slot_editor .cancel').click(function()
    {
      $('#sympal_content_slot_<?php echo $contentSlot->getId() ?>').find('.editor').hide();
      $('#sympal_content_slot_<?php echo $contentSlot->getId() ?>').find('.value').show()
    });
  });
</script>