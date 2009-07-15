<?php $filters = $sf_user->getAttribute('sympal_content.filters', array(), 'admin_module') ?>

<?php $types = array() ?>

<?php foreach (sfSympalCache::getContentTypes() as $id => $contentType): ?>
  <?php if ($filters['content_type_id'] == $id): ?>
    <?php $types[] = '<strong>'.$contentType.'</strong>' ?>
  <?php else: ?>
    <?php $types[] = link_to($contentType, '@sympal_content_list_type?type='.$id) ?>
  <?php endif; ?>
<?php endforeach; ?>

<?php echo implode(' | ', $types) ?>