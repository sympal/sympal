<?php use_helper('Entity') ?>

<?php $record = $entity->getRecord() ?>

<div id="header">
  <h1><?php echo entity_slot($entity, 'title', 'Text') ?></h1>
</div>

<div id="body">
  <?php echo entity_slot($entity, 'body', 'Markdown') ?>
</div>

<?php if (!$record->disable_comments && sfSympalConfig::get('Comments', 'enabled') && sfSympalConfig::get('Page', 'enable_comments')): ?>
  <?php echo get_component('sympal_comments', 'for_entity', array('entity' => $entity)) ?>
<?php endif; ?>