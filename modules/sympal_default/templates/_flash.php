<?php if ($sf_user->hasFlash('notice') || $sf_user->hasFlash('error')): ?>
  <div id="sympal_flash">
    <?php if ($sf_user->hasFlash('notice')): ?>
      <div class="notice"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>

    <?php if ($sf_user->hasFlash('error')): ?>
      <div class="error"><?php echo $sf_user->getFlash('error') ?></div>
    <?php endif; ?>
  </div>

  <script type="text/javascript">
  var interval = setInterval(hideFlash, 5000);
  function hideFlash()
  {
	   document.getElementById('sympal_flash').style.display = 'none';
  }
  </script>
<?php endif; ?>