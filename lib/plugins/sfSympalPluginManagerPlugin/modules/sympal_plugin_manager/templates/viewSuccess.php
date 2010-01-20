<h1>Sympal Plugin Manager</h1>

<div id="sympal_plugin_manager">
  <div id="view">
    <?php echo link_to(__('Back to Plugins'), '@sympal_plugin_manager') ?>

    <div id="header">
      <?php echo image_tag($sf_sympal_plugin->getImage(), 'align=right') ?>

      <h2><?php echo $sf_sympal_plugin->getTitle() ?></h2>

      <?php if ($description = $sf_sympal_plugin->getDescription()): ?>
        <div id="description">
          <?php use_helper('Text') ?>
          <p><?php echo truncate_text(strip_tags($description), 200) ?></p>
        </div>
      <?php endif; ?>
    </div>

    <div id="actions">
      <h3><?php echo __('Plugin Actions') ?></h3>

      <?php echo get_partial('sympal_plugin_manager/actions', array('sf_sympal_plugin' => $sf_sympal_plugin, 'additional' => true)) ?>
    </div>
  </div>
</div>