<?php $contentTypes = Doctrine_Core::getTable('ContentType')->findAll() ?>
<div id="top_menu">
  <ul>
    <li>Content Types: </li>
    <?php foreach ($contentTypes as $contentType): ?>
      <li<?php if ($sf_user->getAttribute('content_type_id') == $contentType->id): ?> class="current"<?php endif; ?>><?php echo link_to($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getId()) ?></li>
    <?php endforeach; ?>
  </ul>
</div>