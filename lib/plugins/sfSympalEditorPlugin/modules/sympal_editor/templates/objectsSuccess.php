<?php sympal_use_jquery() ?>
<script type="text/javascript" src="<?php echo javascript_path('/sfSympalEditorPlugin/js/objects.js') ?>"></script>

<div id="sympal_objects_container">
  <h1><?php echo __('Object Browser') ?></h1>

  <p>
    <?php echo __(
      'Browse your objects below and insert objects into the currently focused editor by '.
      'just clicking the object you want to choose.') ?>
  </p>

  <div id="content_types">
    <?php if (count($slotKeys)): ?>
      <h2><?php echo __('Classes') ?></h2>
      <ul id="editor_slot_object_classes">
        <?php foreach ($slotKeys as $key): ?>
          <li>
            <?php if ($slotKey === $key): ?>
              <strong><?php echo $key ?></strong>
            <?php else: ?>
              <?php echo jq_link_to_remote($key, array(
                'url' => url_for('@sympal_editor_objects?slot_key='.$key),
                'update' => 'sympal_objects_container'
              )) ?>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <h2><?php echo __('No objects slots have been defined') ?></h2>
    <?php endif; ?>
  </div>

  <div id="sympal_objects_list">
    <?php if ($slotKey): ?>
      <h2><?php echo $slotKey ?> <?php echo __('Objects') ?></h2>
      <ul>
        <?php if (count($objects)): ?>

          <?php foreach ($objects as $object): ?>
            <li rel="<?php echo $slotKey.':'.$object->id ?>">
              <?php echo image_tag('/sfSympalPlugin/images/file_icon.gif') ?>

              <a href="#"><?php echo $object ?></a>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <?php echo __('Nothing found') ?>
        <?php endif; ?>
      </ul>
    <?php endif; ?>
  </div>

  <a class="sympal_close_menu"><?php echo __('Close') ?></a>
</div>