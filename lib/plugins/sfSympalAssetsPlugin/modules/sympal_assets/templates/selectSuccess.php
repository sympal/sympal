<div id="sympal_assets_container">
  <link rel="stylesheet" type="text/css" media="screen" href="<?php echo stylesheet_path('/sfSympalAssetsPlugin/css/assets.css') ?>" />
  <script type="text/javascript" src="<?php echo javascript_path('/sfSympalAssetsPlugin/js/select.js') ?>"></script>

  <h2><?php echo $directory ? $directory : '/' ?></h2>

  <ul id="sympal_assets_list">
    <?php if($parentDirectory): ?>
      <li class="up">
        <?php echo link_to(image_tag('/sfSympalAssetsPlugin/images/icons/up.png'), $currentRoute, array_merge($sf_data->getRaw('currentParams'), array('dir' => $parentDirectory))) ?>
      </li>
    <?php endif ?>

    <?php foreach($directories as $dir): ?>
      <li class="folder">
        <?php echo link_to(image_tag('/sfSympalAssetsPlugin/images/icons/folder.png'), $currentRoute, array_merge($sf_data->getRaw('currentParams'), array('dir' => urlencode($directory.'/'.pathinfo($dir, PATHINFO_BASENAME))))) ?>
        <div class="name"><?php echo $dir ?></div>
        <div class="action"><?php echo link_to('delete', 'sympal_assets_delete_directory', array('sf_method' => 'delete', 'directory' => urlencode($directory.'/'.$dir)), array('class' => 'delete', 'title' => sprintf(__('Delete folder "%s"'), $directory))) ?></div>
      </li>
    <?php endforeach ?>

    <?php foreach($assets as $asset): ?>
      <li id="<?php echo $asset->getId() ?>" class="asset">
        <?php include_partial('sympal_assets/asset_box', array('asset' => $asset)) ?>
      </li>
    <?php endforeach ?>
  </ul>
</div>