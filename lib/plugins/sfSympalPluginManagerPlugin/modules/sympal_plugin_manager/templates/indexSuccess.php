<h2>Sympal Plugin Manager</h2>

<p>
  From this interface you can browse the available Sympal 
  plugins and click to install. You can also view all installed 
  Sympal plugins and one click install them.
</p>

<?php if ($count = count($availablePlugins)): ?>
  <h3><?php echo $count ?> Sympal plugin(s) found.</h3>

  <ul>
    <?php foreach ($availablePlugins as $plugin): ?>
      <li>
        <strong><?php echo $plugin ?></strong> - 
        <?php if (sfSympalTools::isPluginInstalled($plugin)): ?>
          <?php echo link_to('Delete', '@sympal_plugin_manager_delete?plugin='.$plugin, 'confirm=Are you sure you wish to delete this plugin?') ?>
          <?php echo link_to('Install', '@sympal_plugin_manager_install?plugin='.$plugin, 'confirm=Are you sure you wish to install this plugin?') ?>
          <?php echo link_to('Un-Install', '@sympal_plugin_manager_uninstall?plugin='.$plugin, 'confirm=Are you sure you wish to uninstall this plugin?') ?>
        <?php else: ?>
          <?php echo link_to('Download', '@sympal_plugin_manager_download?plugin='.$plugin, 'confirm=Are you sure you wish to download this plugin?') ?>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p><strong>No Sympal plugins found</strong></p>
<?php endif; ?>