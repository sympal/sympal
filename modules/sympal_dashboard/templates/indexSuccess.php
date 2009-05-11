<?php set_sympal_title(__('Sympal Dashboard')) ?>
<?php use_stylesheet('/sfSympalPlugin/css/dashboard') ?>

<div id="sympal-dashboard">
  <h1><?php echo __('Sympal Dashboard') ?></h1>

  <div id="right">
    <?php $right = $right instanceof sfOutputEscaper ? $right->getRawValue()->render():$right->render() ?>
    <?php echo $right ?>
  </div>

  <div id="boxes">
    <h2><?php echo __('Sympal Management') ?></h2>

    <?php $boxes = $boxes instanceof sfOutputEscaper ? $boxes->getRawValue()->render():$boxes->render() ?>
    <?php echo $boxes ?>
  </div>
</div>