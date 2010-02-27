<?php
/**
 * Partial responsible for injecting the new content from a saved slot
 * back into the content div for that slot
 */
?>

<script type="text/javascript">
  $(document).ready(function(){
    $('#sympal_slot_wrapper_<?php echo $contentSlot->id ?> .sympal_slot_content').html('<?php echo str_replace("\n", '\n', addslashes($contentSlot->render())) ?>');
  });
</script>