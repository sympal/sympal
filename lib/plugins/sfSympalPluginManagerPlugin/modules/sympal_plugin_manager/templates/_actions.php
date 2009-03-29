<ul class="actions">
  <?php if (sfSympalPluginToolkit::isPluginDownloaded($plugin)): ?>
    <li><?php echo image_tag('/sf/sf_admin/images/delete.png').' '.link_to('Delete', '@sympal_plugin_manager_delete?plugin='.$plugin) ?></li>

    <?php if (sfSympalPluginToolkit::isPluginInstalled($plugin)): ?>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/uninstall.png').' '.link_to('Uninstall', '@sympal_plugin_manager_uninstall?plugin='.$plugin) ?></li>
    <?php else: ?>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/install.png').' '.link_to('Install', '@sympal_plugin_manager_install?plugin='.$plugin) ?></li>
    <?php endif; ?>
  <?php else: ?>
    <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/download.png').' '.link_to('Download', '@sympal_plugin_manager_download?plugin='.$plugin) ?></li>
  <?php endif; ?>
</ul>