<?php set_sympal_title(__('Sympal Dashboard')) ?>
<?php use_stylesheet('/sfSympalPlugin/css/dashboard') ?>

<div id="sympal-dashboard">
  <div id="boxes">
    <h1><?php echo __('Sympal Dashboard') ?></h1>

    <p><?php echo __('Hello <strong>%name%</strong>! Welcome to your Sympal Dashboard. Below are a set of icons that you can use to navigate around Sympal.', array('%name%' => $sf_user->getName())) ?></p>

    <?php echo $boxes->render() ?>
  </div>
</div>