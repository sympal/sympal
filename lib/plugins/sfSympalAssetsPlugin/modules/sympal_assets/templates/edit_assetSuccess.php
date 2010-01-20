<?php use_helper('jQuery') ?>
<div id="sf_admin_container">
  <div>
    <?php if ($sf_request->isXmlHttpRequest()): ?>
    <?php echo jq_link_to_remote(__('Back to list', array(), 'sf_admin'), array(
      'url' => url_for('@sympal_assets_select'),
      'update' => 'sympal_assets_container',
      'title' => __('Back to list', array(), 'sf_admin'),
      'method' => 'get'
    )) ?>
    <?php else: ?>
    <?php echo link_to(__('Back to list', array(), 'sf_admin'), '@sympal_assets') ?>
    <?php endif ?>
  </div>
  <h1><?php echo __('Editing Asset "%asset%"', array('%asset%' => $asset->getName())) ?></h1>

  <div id="sympal_edit_asset">
    <div class="sf_admin_form">
      <?php if ($isAjax): ?>
        <script type="text/javascript">
        $(function() {
          $('#sympal_edit_asset form').submit(function() {
            $(this).ajaxSubmit({
              target: '#sympal_assets_container'
            });
            return false;
          });
        });
        </script>
      <?php endif; ?>

      <?php echo $form->renderFormTag(url_for('sympal_assets_edit_asset', $asset), array('enctype' => 'multipart/form-data')) ?>
        <?php echo $form->renderHiddenFields() ?>
        <input type="hidden" name="is_ajax" value="<?php echo $isAjax ?>" />

        <h2><?php echo __('Upload New File') ?></h2>
        <?php echo $form['file'] ?>
        <input type="submit" value="<?php echo __('Upload') ?>" />

        <h2><?php echo __('Rename') ?></h2>
        <?php echo $form['new_name'] ?>
        <input type="submit" value="<?php echo __('Save') ?>" />

        <h2><?php echo __('Resize') ?></h2>
        <?php echo $form['width'] ?> x 
        <?php echo $form['height'] ?>
        <input type="submit" value="<?php echo __('Save') ?>" />
      </form>
      
      <?php if ($asset->isImage()): ?>
        <h2><?php echo __('Current Crop') ?></h2>
        <?php echo image_tag($asset->getUrl()) ?>

        <h2><?php echo __('Crop Original Image') ?></h2>
        <p>
          <?php echo image_tag($asset->getOriginal()->getUrl(), array('id' => 'jcrop_target')) ?>
        </p>
        <input type="button" id="sympal_save_crop" value="<?php echo __('Save Crop') ?>" />

        <?php sympal_use_jquery() ?>
        <?php sympal_use_javascript('/sfSympalPlugin/js/jquery.Jcrop.js') ?>
        <?php sympal_use_stylesheet('/sfSympalPlugin/css/jquery.Jcrop.css') ?>

        <script type="text/javascript">
        $(function() {
          var url;
          $('#jcrop_target').Jcrop({
          	onChange: sympalSaveImageCrop
          });
          $('#sympal_save_crop').click(function() {
            <?php if ($isAjax): ?>
            $('#sympal_assets_container').load(url);
            <?php else: ?>
            $.get(url);
            location.href = '<?php echo url_for ('@sympal_assets?dir='.$asset->getRelativePathDirectory()) ?>';
            <?php endif; ?>
          });
          function sympalSaveImageCrop(c)
          {
            url = '<?php echo url_for('@sympal_assets_save_image_crop?id='.$asset->getId().'&x=X&y=Y&x2=X2&y2=Y2&w=W&h=H') ?>';
            url = url.replace('X', c.x);
            url = url.replace('Y', c.y);
            url = url.replace('X2', c.x2);
            url = url.replace('Y2', c.y2);
            url = url.replace('W', c.w);
            url = url.replace('H', c.h);
          }
        });
        
        </script>
      <?php endif; ?>
    </div>
  </div>
</div>