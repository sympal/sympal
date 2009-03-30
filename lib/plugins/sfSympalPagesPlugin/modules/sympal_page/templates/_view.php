<?php echo get_sympal_breadcrumbs($menuItem, $content) ?>

<?php $record = $content->getRecord() ?>

<h2><?php echo get_sympal_column_content_slot($content, 'title') ?></h2>

<?php echo get_sympal_content_slot($content, 'body', 'Markdown') ?>

<?php if (!$record->disable_comments && sfSympalConfig::get('sfSympalCommentsPlugin', 'enabled') && sfSympalConfig::get('Page', 'enable_comments')): ?>
  <?php echo get_sympal_comments($content) ?>
<?php endif; ?>