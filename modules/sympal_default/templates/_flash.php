<?php if ($sf_user->hasFlash('notice') || $sf_user->hasFlash('error')): ?>
  <div id="sympal_flash">
    <?php if ($sf_user->hasFlash('notice')): ?>
      <?php foreach ($sf_user->getFlashArray('notice') as $notice): ?>
        <div class="notice"><?php echo $notice ?></div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($sf_user->hasFlash('error')): ?>
      <?php foreach ($sf_user->getFlashArray('error') as $error): ?>
        <div class="error"><?php echo $error ?></div>
      <?php endforeach; ?>
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