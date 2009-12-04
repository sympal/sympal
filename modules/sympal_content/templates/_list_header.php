<?php $types = array() ?>

<?php foreach (sfSympalCache::getContentTypes() as $id => $contentType): ?>
  <?php $types[] = link_to($contentType, '@sympal_content_list_type?type='.$id) ?>
<?php endforeach; ?>

<?php echo implode(' | ', $types) ?>