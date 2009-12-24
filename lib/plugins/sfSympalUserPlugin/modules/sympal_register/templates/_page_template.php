<h1><?php echo get_sympal_content_slot($content, 'title', 'Text') ?></h1>

<?php echo get_sympal_content_slot($content, 'body', 'Markdown') ?>

<?php echo get_component('sfGuardRegister', 'form') ?>