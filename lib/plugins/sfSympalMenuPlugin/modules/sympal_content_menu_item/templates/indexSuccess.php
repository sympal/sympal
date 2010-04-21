<div id="sf_admin_container">
  <?php if ($form->isNew()): ?>
    <h1><?php echo __('Add the "%content%" %type% to your Menu', array(
      '%content%' => $content, '%type%' => $content['Type']['label'])) ?></h1>
  <?php else: ?>
    <h1><?php echo __('Editing the "%content%" %type% Menu Item', array(
      '%content%' => $content, '%type%' => $content['Type']['label'])) ?></h1>
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
          <input type="submit" name="save" value="<?php echo __('Save', array(), 'sf_admin') ?>" />
          <input type="button" name="cancel" value="<?php echo __('Cancel', array(), 'sf_admin') ?>" onClick="javascript: history.go(-1);" />
        </div>
      </form>
    </div>
  </div>
</div>