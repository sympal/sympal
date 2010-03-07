<script type="text/javascript">
  $(function() {
    $('#sympal_clear_cache_fancybox').fancybox();
  });
</script>

<?php sympal_use_jquery() ?>
<?php sympal_use_javascript('/sfSympalPlugin/fancybox/jquery.fancybox.js') ?>
<?php sympal_use_stylesheet('/sfSympalPlugin/fancybox/jquery.fancybox.css') ?>

<?php sympal_use_stylesheet('/sfSympalUpgradePlugin/css/upgrade.css') ?>

<div id="sf_admin_container">
  
  <h1><?php echo __('Sympal Dashboard') ?></h1>

  <?php if ($hasNewVersion): ?>
    <div class="sympal_new_version_box">
      Sympal <?php echo $upgrade->getLatestVersion() ?> is available! Click <?php echo link_to('here', '@sympal_upgrade') ?> for information on upgrading!
    </div>
  <?php endif; ?>

  <p><?php echo __('Hello %name%! Welcome to your Sympal Dashboard.', array('%name%' => '<strong>'.$sf_user->getName().'</strong>')) ?></p>
  <p><?php echo __('Here is a list of indicators for your installation:') ?></p>

   <?php echo $indicators ?>

</div>
