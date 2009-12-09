<?php use_stylesheet('/sfSympalPlugin/css/global.css', 'first') ?> 
<?php use_stylesheet('/sfSympalPlugin/css/default.css', 'first') ?> 

<div id="sf_admin_container">
  <?php if ($form->isNew()): ?>
    <h1>Add the "<?php echo $content ?>" <?php echo $content['Type']['label'] ?> to your Menu</h1>
  <?php else: ?>
    <h1>Edit the "<?php echo $content ?>" <?php echo $content['Type']['label'] ?> Menu Item</h1>
  <?php endif; ?>

  <?php echo $form->renderFormTag(url_for('@sympal_content_menu_item?id='.$content->getId())) ?>
    <?php echo $form->renderHiddenFields() ?>

    <div id="sf_admin_content">
      <?php echo $form->renderGlobalErrors() ?>
      <?php echo get_partial('sympal_default/render_form', array('form' => $form)) ?>
    </div>

    <div class="black_bar">
      <input type="submit" name="save" value="Save" />
      <?php echo button_to('Cancel', $content->getEditRoute()) ?>
    </div>
  </form>
</div>