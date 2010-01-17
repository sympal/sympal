<?php echo get_sympal_breadcrumbs($menuItem, $content) ?>

<?php $record = $content->getRecord() ?>

<h1><?php echo get_sympal_content_slot($content, 'title') ?></h1>

<?php echo get_sympal_content_slot($content, 'body', 'Markdown') ?>