<?php $contentTypes = Doctrine_Core::getTable('sfSympalContentType')->findAll() ?>
<div id="sympal_content_type_menu">
  <h3><?php echo __('Change Content Type') ?></h3>
  <ul>
    <?php foreach ($contentTypes as $contentType): ?>
      <li<?php if ($sf_user->getAttribute('content_type_id') == $contentType->id): ?> class="current"<?php endif; ?>><?php echo link_to($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getId()) ?></li>
    <?php endforeach; ?>
  </ul>
</div>