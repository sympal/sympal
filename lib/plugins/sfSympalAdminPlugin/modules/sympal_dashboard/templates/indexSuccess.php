<?php set_sympal_title(__('Sympal Dashboard')) ?>
<?php use_stylesheet('/sfSympalPlugin/css/dashboard') ?>
<?php use_stylesheet('/sfSympalPlugin/css/upgrade') ?>

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
</div>