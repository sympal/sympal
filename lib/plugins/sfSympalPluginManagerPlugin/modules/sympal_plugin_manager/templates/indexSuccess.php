<h2>Sympal Plugin Manager</h2>

<p>
  From this interface you can browse the available Sympal 
  plugins and click to install. You can also view all installed 
  Sympal plugins and one click install them.
</p>

<?php if ($count = count($addonPlugins)): ?>
  <h3><?php echo $count ?> Sympal addon plugin(s) found.</h3>

  <ul>
    <?php foreach ($addonPlugins as $plugin): ?>
      <li>
        <strong><?php echo link_to(sfSympalTools::getShortPluginName($plugin), '@sympal_plugin_manager_view?plugin='.$plugin) ?></strong> - 
        <?php if (sfSympalTools::isPluginInstalled($plugin)): ?>
          <?php echo link_to('Delete', '@sympal_plugin_manager_delete?plugin='.$plugin, 'confirm=Are you sure you wish to delete this plugin?') ?>
          <?php echo link_to('Install', '@sympal_plugin_manager_install?plugin='.$plugin, 'confirm=Are you sure you wish to install this plugin?') ?>
          <?php echo link_to('Uninstall', '@sympal_plugin_manager_uninstall?plugin='.$plugin, 'confirm=Are you sure you wish to uninstall this plugin?') ?>
        <?php else: ?>
          <?php echo link_to('Download', '@sympal_plugin_manager_download?plugin='.$plugin, 'confirm=Are you sure you wish to download this plugin?') ?>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p><strong>No Sympal plugins found</strong></p>
<?php endif; ?>

<?php slot('sympal_right_sidebar') ?>
  <h2>Core Sympal Plugins</h2>
  <?php if ($corePlugins): ?>
    <ul>
      <?php foreach ($corePlugins as $plugin): ?>
        <li><?php echo sfSympalTools::getShortPluginName($plugin) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p><strong></strong></p>
  <?php endif; ?>

  <h2>Installed Sympal Plugins</h2>
  <?php if ($installedPlugins): ?>
    <ul>
      <?php foreach ($installedPlugins as $plugin): ?>
        <li><?php echo sfSympalTools::getShortPluginName($plugin) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p><strong>No Plugins Installed</strong></p>
  <?php endif; ?>
<?php end_slot() ?>