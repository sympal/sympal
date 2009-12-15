<form style="padding: 10px;" method="post" action="<?php echo url_for('@sympal_change_language_form') ?>">
  <?php echo $form['language']->renderLabel() ?>
  <?php echo $form['language'] ?>
  <input type="submit" value="<?php echo __('Go') ?>" />
</form>