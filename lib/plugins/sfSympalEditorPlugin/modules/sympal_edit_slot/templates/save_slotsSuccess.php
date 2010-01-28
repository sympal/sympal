
<?php /* Add red border around input and textareas of failed content slots */ ?>
<?php foreach ($failedContentSlots as $contentSlot): ?>
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor input, #sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor textarea').css('border', '1px solid red');
<?php endforeach; ?>

<?php /* Updated rendered value of successfully content slots and removed red border */ ?>
<?php foreach ($contentSlots as $contentSlot): ?>
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?> .value').html('<?php echo str_replace("\n", ' ', $contentSlot->render()) ?>');
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor input, #sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor textarea').css('border', '1px solid #ccc');
<?php endforeach; ?>

<?php /* Output the error string if errors occurred */ ?>
<?php if ($errorString): ?>
  alert('<?php echo $errorString ?>');
<?php endif; ?>