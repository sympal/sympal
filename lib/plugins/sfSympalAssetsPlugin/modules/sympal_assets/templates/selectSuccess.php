<link rel="stylesheet" type="text/css" media="screen" href="<?php echo stylesheet_path('/sfSympalAssetsPlugin/css/select.css') ?>" />
<script type="text/javascript" src="<?php echo javascript_path('/sfSympalAssetsPlugin/js/select.js') ?>"></script>
<?php use_helper('jQuery') ?>

<div id="sympal_assets_container" class="sympal_form">
  <h1><?php echo __('Asset Browser') ?></h1>

  <p>
    <?php echo
__('Browse your assets below and insert them into the currently focused editor by
just clicking the asset you want to insert. You can control where the asset is 
inserted by positioning the cursor in the editor. You may also upload new assets
and create directories below.') ?>
  </p>

  <input type="hidden" id="current_url" value="<?php echo $sf_request->getUri() ?>" />
  <input type="button" class="sympal_assets_upload" value="<?php echo __('Upload Asset') ?>" />
  <input type="button" class="sympal_create_directory" value="<?php echo __('Create Directory') ?>" />

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
        <?php echo jq_link_to_remote(image_tag('/sfSympalPlugin/images/delete.png'), array(
            'url' => url_for('@sympal_assets_delete_directory?directory='.urlencode($directory.'/'.pathinfo($dir, PATHINFO_BASENAME))),
            'update' => 'sympal_assets_container',
            'title' => __('Delete directory "%dir%"', array('%dir%' => $dir)),
            'method' => 'get'
          )
        ) ?>

        <?php echo image_tag('/sfSympalAssetsPlugin/images/icons/folder.png', 'width=25') ?>
        <?php echo link_to($dir, $currentRoute, array_merge($sf_data->getRaw('currentParams'), array('dir' => urlencode($directory.'/'.pathinfo($dir, PATHINFO_BASENAME)))), array('class' => 'go')) ?>
      </li>
    <?php endforeach ?>

    <?php foreach($assets as $asset): ?>
      <li id="sympal_asset_<?php echo $asset->getSlug() ?>" title="<?php echo $asset->getName() ?>" class="asset">
        <?php echo jq_link_to_remote(image_tag('/sfSympalPlugin/images/edit.png'), array(
            'url' => url_for('sympal_assets_edit_asset', $asset),
            'update' => 'sympal_assets_container',
            'title' => __('Edit file "%file%"', array('%file%' => $asset->getName())),
            'method' => 'get'
          )
        ) ?>

        <?php echo jq_link_to_remote(image_tag('/sfSympalPlugin/images/delete.png'), array(
            'url' => url_for('sympal_assets_delete_asset', $asset),
            'update' => 'sympal_assets_container',
            'title' => __('Delete file "%file%"', array('%file%' => $asset->getName())),
            'method' => 'get'
          )
        ) ?>

        <?php echo link_to($asset->getName(), $asset->getUrl(), array(
          'class' => sprintf('insert %s', $asset->getEmbedOptions(true)),
          'title' => $asset->slug,
        )) ?>

        <?php if ($asset->isImage()): ?>
          <?php echo image_tag($asset->getUrl(), 'class="preview" style="display: none;"') ?>
        <?php endif; ?>
      </li>
    <?php endforeach ?>
  </ul>

  <a class="sympal_close_menu"><?php echo __('Close') ?></a>
</div>