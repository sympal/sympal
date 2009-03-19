<?php use_helper('Entity') ?>

<?php echo get_menu_item_breadcrumbs($menuItem) ?>

<?php $record = $entity->getRecord() ?>

<h2><?php echo entity_slot($entity, 'title', 'Text') ?></h2>

<?php echo entity_slot($entity, 'body', 'Markdown') ?>

<?php if (!$record->disable_comments && sfSympalConfig::get('Comments', 'enabled') && sfSympalConfig::get('Page', 'enable_comments')): ?>
  <?php echo get_component('sympal_comments', 'for_entity', array('entity' => $entity)) ?>
<?php endif; ?>