<div id="sympal_slot_flash">
  <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="sympal_notice"><?php echo $sf_user->getFlash('notice') ?></div>
  <?php endif; ?>

  <?php if ($sf_user->hasFlash('error')): ?>
    <div class="sympal_error"><?php echo $sf_user->getFlash('error') ?></div>
  <?php endif; ?>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    setTimeout("jQuery('#sympal_slot_flash').hide()", 3000);
  });
</script>