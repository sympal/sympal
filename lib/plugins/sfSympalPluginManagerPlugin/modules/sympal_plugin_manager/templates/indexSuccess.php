<?php set_sympal_title('Sympal Plugin Manager') ?>
<?php use_stylesheet('/sfSympalPluginManagerPlugin/css/plugin_manager') ?>

<div id="sympal_plugin_manager">
  <div id="list">
    <h2>Sympal Plugin Manager</h2>

    <?php if ($count = count($addonPlugins)): ?>
      <h3><?php echo $count ?> Sympal addon plugin(s) found.</h3>

      <table>
        <thead>
          <tr>
            <th>Plugin</th>
            <th>Actions</th>
          </tr>
        </thead>
        <?php foreach ($addonPlugins as $plugin): ?>
          <tr>
            <td><strong><?php echo link_to(sfSympalPluginToolkit::getShortPluginName($plugin), '@sympal_plugin_manager_view?plugin='.$plugin) ?></strong></td>
            <td>
              <?php echo get_partial('sympal_plugin_manager/actions', array('plugin' => $plugin)) ?>
            </tr>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php else: ?>
      <p><strong>No Sympal plugins found</strong></p>
    <?php endif; ?>
  </div>
</div>

<?php slot('sympal_right_sidebar') ?>
  <?php echo get_partial('sympal_plugin_manager/sidebar_plugin_list', array('link' => true, 'title' => 'Downloaded Sympal Plugins', 'plugins' => $installedPlugins)) ?>
  <?php echo get_partial('sympal_plugin_manager/sidebar_plugin_list', array('title' => 'Core Sympal Plugins', 'plugins' => $corePlugins)) ?>
<?php end_slot() ?>