<script type="text/javascript">
$(function() {
  $('#sympal_clear_cache_fancybox').fancybox();
});
</script>

<?php sympal_use_jquery() ?>
<?php sympal_use_javascript('/sfSympalPlugin/fancybox/jquery.fancybox.js') ?>
<?php sympal_use_stylesheet('/sfSympalPlugin/fancybox/jquery.fancybox.css') ?>

<?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/dashboard.css') ?>
<?php sympal_use_stylesheet('/sfSympalUpgradePlugin/css/upgrade.css') ?>

<?php if ($hasNewVersion): ?>
  <div class="sympal_new_version_box">
    Sympal <?php echo $upgrade->getLatestVersion() ?> is available! Click <?php echo link_to('here', '@sympal_upgrade') ?> for information on upgrading!
  </div>
<?php endif; ?>

<div id="sympal-dashboard">
  <div id="sympal-dashboard-right">
    <?php echo $dashboardRight ?>
  </div>

  <div id="sympal-dashboard-left">
    <h1><?php echo __('Sympal Dashboard') ?></h1>

    <p><?php echo __('Hello %name%! Welcome to your Sympal Dashboard. Below you can navigate the functionality and administer your site.', array('%name%' => '<strong>'.$sf_user->getName().'</strong>')) ?></p>

    <?php echo get_sympal_admin_menu_object('sfSympalMenuDashboard') ?>
  </div>
  <br style="clear: left;" />
</div>