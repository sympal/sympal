<?php echo get_sympal_breadcrumbs($menuItem, null, 'Change Password', true) ?>

<h2>Hello <?php echo $profile->getName() ?></h2>

<h3>Enter your new password in the form below.</h3>

<form action="<?php echo url_for('@sympal_forgot_password_change_save?unique_key='.$sf_request->getParameter('unique_key')) ?>" method="POST">
  <table>
    <?php echo $form ?>
  </table>
  <input type="submit" name="change" value="Change" />
</form?