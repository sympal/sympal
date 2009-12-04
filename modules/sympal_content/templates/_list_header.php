<div id="top_menu">
  <ul>
    <?php foreach (sfSympalCache::getContentTypes() as $id => $contentType): ?>
      <li><?php echo link_to($contentType, '@sympal_content_list_type?type='.$id) ?></li>
    <?php endforeach; ?>
  </ul>
</div>