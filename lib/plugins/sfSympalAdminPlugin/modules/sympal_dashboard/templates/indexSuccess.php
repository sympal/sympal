<?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/dashboard.css') ?>
<?php sympal_use_stylesheet('/sfSympalUpgradePlugin/css/upgrade.css') ?>

<?php if ($hasNewVersion): ?>
  <div class="sympal_new_version_box">
    Sympal <?php echo $upgrade->getLatestVersion() ?> is available! Click <?php echo link_to('here', '@sympal_upgrade') ?> for information on upgrading!
  </div>
<?php endif; ?>

<?php if ($sf_request->isXmlHttpRequest()): ?>
  <p><?php echo link_to(__('Go to the Sympal admin area for your site.'), '@sympal_dashboard') ?></p>
<?php endif; ?>

<div id="sympal-dashboard">
  <h1><?php echo __('Sympal Dashboard') ?></h1>

  <p><?php echo __('Hello %name%! Welcome to your Sympal Dashboard. Below you can navigate the functionality and administer your site.', array('%name%' => '<strong>'.$sf_user->getName().'</strong>')) ?></p>

  <?php echo get_sympal_admin_menu_object('sfSympalMenuDashboard') ?>
  <br style="clear: left;" />
</div>