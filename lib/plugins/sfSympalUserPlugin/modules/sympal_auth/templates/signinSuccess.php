<?php echo get_sympal_breadcrumbs(array('Home' => '@homepage', 'Signin' => null)) ?>

<h2><?php echo sfSympalContext::getInstance()->getSite()->getTitle(); ?></h2>

<?php echo get_partial('sympal_auth/signin_form', array('form' => $form)) ?>