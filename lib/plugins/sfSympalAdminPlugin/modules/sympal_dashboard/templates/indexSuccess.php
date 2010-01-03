<?php if ($isAjax): ?>
  <link rel="stylesheet" type="text/css" media="screen" href="<?php echo stylesheet_path('/sfSympalAdminPlugin/css/dashboard') ?>" />
  <link rel="stylesheet" type="text/css" media="screen" href="<?php echo stylesheet_path('/sfSympalUpgradePlugin/css/upgrade') ?>" />
<?php else: ?>
  <?php sympal_use_stylesheet('/sfSympalAdminPlugin/css/dashboard') ?>
  <?php sympal_use_stylesheet('/sfSympalUpgradePlugin/css/upgrade') ?>
<?php endif; ?>

<?php if ($hasNewVersion): ?>
  <div class="sympal_new_version_box">
    Sympal <?php echo $upgrade->getLatestVersion() ?> is available! Click <?php echo link_to('here', '@sympal_upgrade') ?> for information on upgrading!
  </div>
<?php endif; ?>

<div id="sympal-dashboard">
  <div id="boxes">
    <h1><?php echo __('Sympal Dashboard') ?></h1>

    <p><?php echo __('Hello <strong>%name%</strong>! Welcome to your Sympal Dashboard. Below you can navigate the functionality and administer your site.', array('%name%' => $sf_user->getName())) ?></p>

    <?php echo $boxes->render() ?>
  </div>
  <?php if ($isAjax): ?>
    <a class="sympal_close_menu">close</a>
  <?php endif; ?>
</div>