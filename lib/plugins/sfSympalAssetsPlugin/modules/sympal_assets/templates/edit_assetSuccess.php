<div id="sf_admin_container">
  <h1>Editing Asset "<?php echo $asset ?>"</h1>

  <h2>"<?php echo $asset->getRelativePath() ?>"</h2>

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

      <?php echo $form->renderFormTag(url_for('sympal_assets_edit_asset', $asset)) ?>
        <?php echo $form->renderHiddenFields() ?>
        <table>
          <?php echo $form ?>
        </table>
        <input type="submit" value="Save" />
      </form>
      
      <?php if ($asset->isImage()): ?>
        <h2>Crop Original Image</h2>
        <?php echo image_tag($asset->getOriginal()->getUrl(), array('id' => 'jcrop_target')) ?>
        <input type="button" id="sympal_save_crop" value="Save Crop" />

        <script type="text/javascript">
        $(function() {
          var url;
          $('#jcrop_target').Jcrop({
          	onChange: sympalSaveImageCrop
          });
          $('#sympal_save_crop').click(function() {
            $('#sympal_assets_container').load(url);
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