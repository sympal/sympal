<h2><?php echo 'Oops! The page you asked for is secure and requires authentication.' ?></h2>

<p><?php echo sfContext::getInstance()->getRequest()->getUri() ?></p>

<h3>You can login below to gain access</h3>

<?php echo get_component('sympal_auth', 'signin_form') ?>