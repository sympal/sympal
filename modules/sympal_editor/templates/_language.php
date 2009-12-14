<form style="padding: 10px;" action="<?php echo url_for('@sympal_change_language') ?>">
  <?php echo $form['language']->renderLabel() ?>
  <?php echo $form['language'] ?>
  <input type="submit" value="<?php echo __('Go') ?>" />
</form>