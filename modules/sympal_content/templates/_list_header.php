<div id="top_menu">
  <ul>
    <li>Content Types: </li>
    <?php foreach (sfSympalContext::getInstance()->getSympalConfiguration()->getContentTypes() as $id => $contentType): ?>
      <li><?php echo link_to($contentType, '@sympal_content_list_type?type='.$id) ?></li>
    <?php endforeach; ?>
  </ul>
</div>