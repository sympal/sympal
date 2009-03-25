<h2><?php echo $title ?></h2>
<?php if ($plugins): ?>
  <ul>
    <?php foreach ($plugins as $plugin): ?>
      <?php if (isset($link) && $link): ?>
        <li><?php echo link_to(sfSympalTools::getShortPluginName($plugin), '@sympal_plugin_manager_view?plugin='.$plugin) ?></li>
      <?php else: ?>
        <li><?php echo sfSympalTools::getShortPluginName($plugin) ?></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p><strong>No Plugins Found</strong></p>
<?php endif; ?>