<?php if ($sf_user->hasCredential('UploadAssets')): ?>
  <fieldset id="sympal_assets_upload">
    <legend><?php echo __('Upload an Asset') ?></legend>
    <form action="<?php echo url_for('sympal_assets_create_asset') ?>" method="post" enctype="multipart/form-data">
      <input type="hidden" id="upload_is_ajax" name="is_ajax" />
      <?php echo $uploadForm ?>
      <input type="submit" class="submit" value="<?php echo __('Upload') ?>" />
    </form>
  </fieldset>
<?php endif; ?>

<?php if ($sf_user->hasCredential('CreateAssetDirectories')): ?>
  <fieldset id="sympal_assets_mkdir">
    <legend><?php echo __('Create a New Directory') ?></legend>
    <form action="<?php echo url_for('sympal_assets_create_directory') ?>" method="post">
      <input type="hidden" id="dir_is_ajax" name="is_ajax" />
      <?php echo $directoryForm ?>
      <input type="submit" class="submit" value="<?php echo __('Create') ?>" />
    </form>
  </fieldset>
<?php endif; ?>

<div style="clear: both;"></div>