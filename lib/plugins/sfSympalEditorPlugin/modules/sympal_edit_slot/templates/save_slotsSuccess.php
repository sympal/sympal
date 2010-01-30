
<?php /* Add red border around input and textareas of failed content slots */ ?>
<?php foreach ($failedContentSlots as $contentSlot): ?>
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor input, #sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor textarea').css('border', '1px solid red');
<?php endforeach; ?>

<?php /* Updated rendered value of successfully content slots and removed red border */ ?>
<?php foreach ($contentSlots as $contentSlot): ?>
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?> .value').html('<?php echo str_replace("\n", ' ', $contentSlot->render()) ?>');
  $('#sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor input, #sympal_content_slot_<?php echo $contentSlot->getId() ?> .editor textarea').css('border', '1px solid #ccc');
<?php endforeach; ?>

<?php /* Output the errors if errors occurred */ ?>
<?php if (count($errors)): ?>
  <!--- !Errors! ---> 

  <?php
  $errorString  = '<h2>'.count($errors).' Error'.(count($errors) > 1 ? 's' : null).' Occurred</h2>';
  $errorString .= '<ul>';
  foreach ($failedContentSlots as $slot)
  {
    $error = $errors[$slot->getName()];
    $errorString .= '<li rel="'.$slot->getId().'" class="sympal_content_slot_error">'.__($slot->getName()).': '.__($error).'</li>';
  }
  $errorString .= '</ul>';
  $errorString .= '<a id="close">'.__('Close').'</a>'
  ?>
  $('#sympal_slot_errors').html('<?php echo $errorString ?>');
  $('#sympal_slot_errors').slideDown();
  $('#sympal_slot_errors_icon').show();
<?php endif; ?>