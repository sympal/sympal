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
        <?php echo get_partial('sympal_default/render_form', array('form' => $form)) ?>
        <input type="submit" value="Save" />
      </form>
    </div>
  </div>
</div>