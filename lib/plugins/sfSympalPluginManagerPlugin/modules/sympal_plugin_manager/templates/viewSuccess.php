<h1>Sympal Plugin Manager</h1>

<div id="sympal_plugin_manager">
  <div id="view">
    <?php echo link_to('Back to Plugins', '@sympal_plugin_manager') ?>

    <div id="header">
      <?php echo image_tag($plugin->getImage(), 'align=right') ?>

      <h2><?php echo $plugin->getTitle() ?></h2>

      <?php if ($description = $plugin->getDescription()): ?>
        <div id="description">
          <?php use_helper('Text') ?>
          <p><?php echo truncate_text(strip_tags($description), 200) ?></p>
        </div>
      <?php endif; ?>
    </div>

    <div id="actions">
      <h3>Plugin Actions</h3>

      <?php echo get_partial('sympal_plugin_manager/actions', array('plugin' => $plugin, 'additional' => true)) ?>
    </div>
  </div>
</div>