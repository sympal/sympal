<?php set_sympal_title('Sympal Dashboard') ?>
<?php use_stylesheet('/sfSympalPlugin/css/dashboard') ?>

<div id="sympal-dashboard">
  <h1>Sympal Dashboard</h1>

  <div id="boxes">
    <h2>Sympal Management</h2>

    <?php echo $boxes ?>
  </div>

  <div id="right">
    <div id="plugins">
      <h2>Plugins</h2>

      <?php echo link_to('Plugin Manager', '@sympal_plugin_manager') ?>

      <?php if (!empty($plugins)): ?>
        <div>
          <ul>
            <?php foreach ($plugins as $plugins): ?>
              <li><?php echo $plugin ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>

    <div id="content-types">
      <h2>Content Types</h2>

      <ul>
        <?php foreach ($contentTypes as $contentType): ?>
          <li>
            <?php echo link_to('Create '.$contentType->getLabel(), '@sympal_content_create_type?type='.$contentType->getSlug()) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>