<?php use_stylesheet('/sfSympalPluginManagerPlugin/css/plugin_manager') ?>

<div id="sympal_plugin_manager">
  <div id="list">
    <h2>Sympal Plugin Manager</h2>

    <?php if ($count = count($addonPlugins)): ?>
      <h3><?php echo $count ?> Sympal addon plugin(s) found.</h3>

      <form action="<?php echo url_for('@sympal_plugin_manager_batch_action') ?>">
        <table>
          <thead>
            <tr>
              <th></th>
              <th>Plugin</th>
              <th>Actions</th>
            </tr>
          </thead>
          <?php foreach ($addonPlugins as $plugin): ?>
            <tr>
              <td><input type="checkbox" name="plugins[]" value="<?php echo $plugin ?>" /></td>
              <td><strong><?php echo link_to(sfSympalPluginToolkit::getShortPluginName($plugin), '@sympal_plugin_manager_view?plugin='.$plugin) ?></strong></td>
              <td>
                <?php echo get_partial('sympal_plugin_manager/actions', array('plugin' => $plugin)) ?>
              </tr>
            </tr>
          <?php endforeach; ?>
        </table>

        <input type="submit" name="download" value="Download" onClick="return confirm('Are you sure you wish to download the selected plugins?');" />
        <input type="submit" name="delete" value="Delete" onClick="return confirm('Are you sure you wish to delete the selected plugins?');" />
        <input type="submit" name="install" value="Install" onClick="return confirm('Are you sure you wish to install the selected plugins?');" />
        <input type="submit" name="uninstall" value="Uninstall" onClick="return confirm('Are you sure you wish to uninstall the selected plugins?');" />
      </form>
    <?php else: ?>
      <p><strong>No Sympal plugins found</strong></p>
    <?php endif; ?>
  </div>
</div>

<?php slot('sympal_right_sidebar') ?>
  <?php echo get_partial('sympal_plugin_manager/sidebar_plugin_list', array('link' => true, 'title' => 'Downloaded Sympal Plugins', 'plugins' => $installedPlugins)) ?>
  <?php echo get_partial('sympal_plugin_manager/sidebar_plugin_list', array('title' => 'Core Sympal Plugins', 'plugins' => $corePlugins)) ?>
<?php end_slot() ?>