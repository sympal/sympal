<?php echo get_sympal_breadcrumbs(array('Home' => '@homepage', 'Signin' => null)) ?>

<h1><?php echo __('Signin') ?></h1>

<?php echo get_partial('sympal_auth/signin_form', array('form' => $form)) ?>