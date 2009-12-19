<h2><?php echo $title ?></h2>
<?php if ($sf_sympal_plugins): ?>
  <ul>
    <?php foreach ($sf_sympal_plugins as $sf_sympal_plugin): ?>
      <?php if (isset($link) && $link): ?>
        <li><?php echo link_to(sfSympalPluginToolkit::getShortPluginName($sf_sympal_plugin), '@sympal_plugin_manager_view?plugin='.$sf_sympal_plugin) ?></li>
      <?php else: ?>
        <li><?php echo sfSympalPluginToolkit::getShortPluginName($sf_sympal_plugin) ?></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p><strong>No Plugins Found</strong></p>
<?php endif; ?>