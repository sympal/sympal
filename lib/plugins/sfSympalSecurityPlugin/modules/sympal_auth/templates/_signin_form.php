<?php use_helper('I18N') ?>

<form action="<?php echo url_for('@sympal_login') ?>" method="post">
  <table>
    <?php echo $form ?>
  </table>

  <input type="submit" value="<?php echo __('sign in') ?>" />
</form>