<div class="icon">
  <?php echo link_to(image_tag($asset->getThumbnailUrl()), $asset->getUrl(), array('target' => '_BLANK', 'class' => $asset->isImage() ? 'fancybox' : null)) ?>

  <div class="name"><?php echo $asset->getName() ?></div>
  <div class="action">
    <span class="size"><?php echo $asset->getSize() ?> Kb</span>

    <?php if($asset->isImage()): ?>
      <span class="dimensions"><?php echo $asset->getWidth() ?>x<?php echo $asset->getHeight() ?></span>
    <?php endif ?>

    <span class="embed_code"><?php echo $asset->getEmbedCode() ?></span>

    <?php echo link_to('edit', 'sympal_assets_edit_asset', $asset,
      array(
        'class' => 'edit',
        'title' => sprintf('Edit file "%s"', $asset->getName())
      )
    ) ?>

    <?php echo link_to('delete', 'sympal_assets_delete_asset', $asset,
      array(
        'class' => 'delete',
        'title' => sprintf('Delete file "%s"', $asset->getName())
      )
    ) ?>
  </div>
</div>
