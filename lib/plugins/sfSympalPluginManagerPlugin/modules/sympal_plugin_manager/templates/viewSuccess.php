<?php use_stylesheet('/sfSympalPluginManagerPlugin/css/plugin_manager') ?>

<h1>Sympal Plugin Manager</h1>

<div id="sympal_plugin_manager">
  <div id="view">
    <?php echo link_to('Back to Plugins', '@sympal_plugin_manager') ?>

    <p>Currently viewing the <strong><?php echo $plugin->getName() ?></strong><?php if ($plugin->getVersion()): ?> <strong><?php echo $plugin->getVersion('release') ?></strong> which is in <strong><?php echo $plugin->getStability('release') ?></strong> state<?php endif; ?>.</p>
    <hr />
    <?php echo get_partial('sympal_plugin_manager/actions', array('plugin' => $plugin->getName())) ?>
    <br/>
    <p><?php echo $plugin->getDescription() ?></p>

    <?php if ($plugin->hasReadme()): ?>
      <p><?php echo $plugin->getReadme() ?></p>
    <?php endif; ?>
  </div>
</div>