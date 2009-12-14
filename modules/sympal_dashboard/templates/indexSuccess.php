<?php set_sympal_title(__('Sympal Dashboard')) ?>
<?php use_stylesheet('/sfSympalPlugin/css/dashboard') ?>

<div id="sympal-dashboard">
  <div id="boxes">
    <h1><?php echo __('Sympal Dashboard') ?></h1>

    <p><?php echo __('Hello <strong>%name%</strong>! Welcome to your Sympal Dashboard. Below you can navigate the functionality and administer your site.', array('%name%' => $sf_user->getName())) ?></p>

    <?php echo $boxes->render() ?>
  </div>
</div>