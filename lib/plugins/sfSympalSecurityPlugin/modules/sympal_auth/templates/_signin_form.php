<?php use_helper('I18N') ?>

<form action="<?php echo url_for('@sympal_login') ?>" method="post">
  <table>
    <?php echo $form ?>
  </table>

  <input type="submit" value="<?php echo __('sign in') ?>" />
  <?php echo link_to('Forgot Password?', '@sympal_forgot_password') ?>
</form>