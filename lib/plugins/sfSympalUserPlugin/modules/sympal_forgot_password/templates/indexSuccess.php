<?php echo get_sympal_breadcrumbs($menuItem, null, null, true) ?>

<h2>Forgot your password?</h2>

<p>Do not worry, we can help you get back in to your account safely! Fill out the form below to retrieve an e-mail with information on how to reset your password.</p>

<form action="<?php echo url_for('@sympal_forgot_password') ?>" method="post">
  <table>
    <?php echo $form ?>
  </table>
  <input type="submit" name="submit" value="Submit" />
</form>