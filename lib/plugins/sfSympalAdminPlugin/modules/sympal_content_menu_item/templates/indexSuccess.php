<div id="sf_admin_container">
  <?php if ($form->isNew()): ?>
    <h1>Add the "<?php echo $content ?>" <?php echo $content['Type']['label'] ?> to your Menu</h1>
  <?php else: ?>
    <h1>Edit the "<?php echo $content ?>" <?php echo $content['Type']['label'] ?> Menu Item</h1>
  <?php endif; ?>

  <div id="sf_admin_content_menu_item">
    <div class="sf_admin_form">
      <?php echo $form->renderFormTag(url_for('@sympal_content_menu_item?id='.$content->getId()), array('method' => 'post')) ?>
        <?php echo $form->renderHiddenFields() ?>

        <div id="sf_admin_content">
          <?php echo $form->renderGlobalErrors() ?>
          <?php echo get_partial('sympal_default/render_form', array('form' => $form)) ?>
        </div>

        <div class="black_bar">
          <input type="submit" name="save" value="Save" />
          <input type="button" name="cancel" value="Cancel" onClick="javascript: history.go(-1);" />
        </div>
      </form>
    </div>
  </div>
</div>