Forgot Password Request for <?php echo $user->getUsername() ?>

Hello <?php echo $user->getName() ?>,<br/><br/>

This e-mail is being sent because you requested information on how to reset your password.<br/><br/>

You can change your password by clicking the below link which is only valid for 24 hours:<br/><br/>

<?php echo link_to('Click to change password', '@sympal_forgot_password_change?unique_key='.$forgot_password->unique_key) ?>