<?php use_stylesheet('/sfSympalPluginManagerPlugin/css/plugin_manager') ?>

<div id="sympal_plugin_manager">
  <div id="view">
    <?php echo link_to('Back to Plugin Manager', '@sympal_plugin_manager') ?>

    <h2><?php echo $plugin->getName() ?></h2>

    <?php echo get_partial('sympal_plugin_manager/actions', array('plugin' => $plugin->getName())) ?>

    <p><?php echo $plugin->getDescription() ?></p>
  </div>
</div>