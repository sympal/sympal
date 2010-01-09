<?php sympal_use_jquery() ?>
<?php sympal_use_javascript('/sfSympalPlugin/fancybox/jquery.fancybox.js') ?>
<?php sympal_use_stylesheet('/sfSympalPlugin/fancybox/jquery.fancybox.css') ?>

<?php sympal_use_stylesheet('/sfSympalAssetsPlugin/css/assets.css') ?>
<?php sympal_use_javascript('/sfSympalAssetsPlugin/js/assets.js') ?>

<?php use_helper('I18N') ?>

<h1><?php echo __('Sympal Assets Manager') ?></h1>

<p><?php echo __('Manage your Sympal project assets below! An asset in Sympal is
any type of file from a PDF document to an image or a video. Upload new assets, create 
directories to store your assets, rename and move your assets and embed or link
to them in your content.') ?></p>

<div id="sympal_assets_forms">
  <?php echo get_partial('sympal_assets/forms', array('uploadForm' => $uploadForm, 'directoryForm' => $directoryForm)) ?>
</div>

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
    <li class="asset">
      <?php include_partial('sympal_assets/asset_box', array('asset' => $asset)) ?>
    </li>
  <?php endforeach ?>
</ul>