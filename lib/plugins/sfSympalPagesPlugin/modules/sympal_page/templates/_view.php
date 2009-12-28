<?php echo get_sympal_breadcrumbs($menuItem, $content) ?>

<?php $record = $content->getRecord() ?>

<h1><?php echo get_sympal_content_slot($content, 'title') ?></h1>

<?php if ($sf_user->isEditMode()): ?>
  <p>
    Created by <strong><?php echo get_sympal_content_slot($content, 'created_by_id', null, 'render_content_author') ?></strong> on 
    <strong><?php echo get_sympal_content_slot($content, 'date_published', null, 'render_content_date_published') ?>.</strong>
  </p>
<?php endif; ?>

<?php echo get_sympal_content_slot($content, 'body', 'Markdown') ?>