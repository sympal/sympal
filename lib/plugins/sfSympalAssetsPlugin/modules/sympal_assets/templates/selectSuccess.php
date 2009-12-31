<link rel="stylesheet" type="text/css" media="screen" href="<?php echo stylesheet_path('/sfSympalAssetsPlugin/css/select.css') ?>" />
<script type="text/javascript" src="<?php echo javascript_path('/sfSympalAssetsPlugin/js/select.js') ?>"></script>

<div id="sympal_assets_container" class="sympal_form">
  <h1>Asset Browser</h1>

  <p>
    Browse your assets below and insert them into the currently focused editor by
    just clicking the asset you want to insert. You can control where the asset is 
    inserted by positioning the cursor in the editor. You may also upload new assets
    and create directories below.
  </p>

  <input type="hidden" id="current_url" value="<?php echo $sf_request->getUri() ?>" />
  <input type="button" class="sympal_assets_upload" value="Upload Asset" />
  <input type="button" class="sympal_create_directory" value="Create Directory" />

  <br/>

  <?php echo get_partial('sympal_assets/forms', array('uploadForm' => $uploadForm, 'directoryForm' => $directoryForm)) ?>

  <h2>
    <?php if($parentDirectory): ?>
      <?php echo link_to(image_tag('/sfSympalAssetsPlugin/images/icons/up.png', 'width=25'), $currentRoute, array_merge($sf_data->getRaw('currentParams'), array('dir' => $parentDirectory)), 'class=up') ?>
    <?php endif ?>
    <?php echo $directory ? $directory : '/' ?>
  </h2>

  <ul id="sympal_assets_list">

    <?php foreach($directories as $dir): ?>
      <li class="folder">
        <?php echo image_tag('/sfSympalAssetsPlugin/images/icons/folder.png', 'width=25') ?>
        <?php echo link_to($dir, $currentRoute, array_merge($sf_data->getRaw('currentParams'), array('dir' => urlencode($directory.'/'.pathinfo($dir, PATHINFO_BASENAME))))) ?>
      </li>
    <?php endforeach ?>

    <?php foreach($assets as $asset): ?>
      <li id="<?php echo $asset->getId() ?>" class="asset">
        <?php echo image_tag($asset->getThumbnailUrl(), 'width=25') ?>
        <?php echo link_to($asset->getName(), $asset->getUrl()) ?>
      </li>
    <?php endforeach ?>
  </ul>
</div>