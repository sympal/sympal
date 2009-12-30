<?php use_helper('jQuery') ?>
<?php use_javascript('/sfSympalPlugin/fancybox/jquery.fancybox.js') ?>
<?php use_stylesheet('/sfSympalPlugin/fancybox/jquery.fancybox.css') ?>

<?php use_stylesheet('/sfSympalAssetsPlugin/css/assets.css') ?>
<?php use_javascript('/sfSympalAssetsPlugin/js/assets.js') ?>

<?php use_helper('I18N') ?>

<h1><?php echo __('Sympal Assets Manager') ?></h1>

<div id="sympal_assets_forms">
  <fieldset id="sympal_assets_upload">
    <legend><?php echo __('Upload an Asset') ?></legend>
    <form action="<?php echo url_for('sympal_assets_create_asset') ?>" method="post" enctype="multipart/form-data">
      <?php echo $uploadForm ?>
      <input type="submit" class="submit" value="<?php echo __('Upload') ?>" />
    </form>
  </fieldset>

  <fieldset id="sympal_assets_mkdir">
    <legend><?php echo __('Create a New Directory') ?></legend>
    <form action="<?php echo url_for('sympal_assets_create_directory') ?>" method="post">
      <?php echo $directoryForm ?>
      <input type="submit" class="submit" value="<?php echo __('Create') ?>" />
    </form>
  </fieldset>
  <div class="clear"></div>
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