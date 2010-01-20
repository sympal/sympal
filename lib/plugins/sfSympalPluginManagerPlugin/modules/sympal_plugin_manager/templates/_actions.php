<ul class="sympal_plugin_actions">
  <?php if ($sf_sympal_plugin->isDownloaded()): ?>
    <li><?php echo image_tag('/sf/sf_admin/images/delete.png').' '.link_to(__('Delete'), $sf_sympal_plugin->getActionRoute('delete')) ?></li>

    <?php if ($sf_sympal_plugin->isInstalled()): ?>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/uninstall.png').' '.link_to(__('Uninstall'), $sf_sympal_plugin->getActionRoute('uninstall')) ?></li>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/install.png').' '.link_to(__('Re-Install'), $sf_sympal_plugin->getActionRoute('install')) ?></li>
    <?php else: ?>
      <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/install.png').' '.link_to(__('Install'), $sf_sympal_plugin->getActionRoute('install')) ?></li>
    <?php endif; ?>
  <?php else: ?>
    <li><?php echo image_tag('/sfSympalPluginManagerPlugin/images/install.png').' '.link_to(__('Install'), $sf_sympal_plugin->getActionRoute('download')) ?></li>
  <?php endif; ?>

  <?php if (isset($additional) && $additional): ?>
    <?php if ($homepage = $sf_sympal_plugin->getHomepage()): ?>
      <li><?php echo image_tag('/sfSympalPlugin/images/new.png') ?> <?php echo link_to(__('Homepage'), $homepage, 'target=_BLANK') ?></li>
    <?php endif; ?>

    <?php if ($ticketing = $sf_sympal_plugin->getTicketing()): ?>
      <li><?php echo image_tag('/sfSympalPlugin/images/new.png') ?> <?php echo link_to(__('Ticketing'), $ticketing, 'target=_BLANK') ?></li>
    <?php endif; ?>
  <?php endif; ?>
</ul>