<ul class="sympal_plugin_actions">
  <?php if ($plugin->isDownloaded()): ?>
    <li><?php echo image_tag('/sf/sf_admin/images/delete.png').' '.link_to('Delete', $plugin->getActionRoute('delete')) ?></li>

    <?php if ($plugin->isInstalled()): ?>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/uninstall.png').' '.link_to('Uninstall', $plugin->getActionRoute('uninstall')) ?></li>
    <?php else: ?>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/install.png').' '.link_to('Install', $plugin->getActionRoute('install')) ?></li>
    <?php endif; ?>
  <?php else: ?>
    <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/download.png').' '.link_to('Download', $plugin->getActionRoute('download')) ?></li>
  <?php endif; ?>

  <?php if (isset($additional) && $additional): ?>
    <?php if ($homepage = $plugin->getHomepage()): ?>
      <li><?php echo image_tag('/sfSympalPlugin/images/new.png') ?> <?php echo link_to('Homepage', $homepage, 'target=_BLANK') ?></li>
    <?php endif; ?>

    <?php if ($ticketing = $plugin->getTicketing()): ?>
      <li><?php echo image_tag('/sfSympalPlugin/images/new.png') ?> <?php echo link_to('Ticketing', $ticketing, 'target=_BLANK') ?></li>
    <?php endif; ?>
  <?php endif; ?>
</ul>