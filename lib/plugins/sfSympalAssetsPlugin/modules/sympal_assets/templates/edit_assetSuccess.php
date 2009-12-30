<div id="sf_admin_container">
  <h1>Editing Asset "<?php echo $asset ?>"</h1>

  <h2><?php echo $asset->getRelativePath() ?></h2>

  <div id="sf_admin_configuration">
    <div class="sf_admin_form">
      <?php echo $form->renderFormTag(url_for('sympal_assets_edit_asset', $asset)) ?>
        <?php echo $form->renderHiddenFields() ?>
        <?php echo get_partial('sympal_default/render_form', array('form' => $form)) ?>
        <input type="submit" value="Save" />
        <?php echo button_to('Go back to Asset Manager', 'sympal_assets') ?>
        <?php echo button_to('Delete Asset', url_for('sympal_assets_delete_asset', $asset)) ?>
      </form>
    </div>
  </div>
</div>