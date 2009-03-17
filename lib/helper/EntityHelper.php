<?php
function entity_slot($entity, $name, $type = 'Text', $defaultValue = '[Double click to edit slot content]')
{
  $user = sfContext::getInstance()->getUser();

  $slotsCollection = $entity->getSlots();
  $slots = array();
  foreach ($slotsCollection as $slot)
  {
    $slots[$slot['name']] = $slot;
  }

  if (!isset($slots[$name]))
  {
    $slot = new EntitySlot();
    $slot->entity_id = $entity->id;

    if (!$slot->exists())
    {
      $type = Doctrine::getTable('EntitySlotType')->findOneByName($type);
      if (!$type)
      {
        $type = new EntitySlotType();
        $type->setName($type);
      }
      $slot->setType($type);
      $slot->setName($name);
    }

    $slot->save();
  } else {
    $slot = $slots[$name];
  }

  if ($slot->getValue())
  {
    $entitySlot = render_entity_slot($slot);
  } else {
    $entitySlot = $defaultValue;
  }

  if (sfSympalTools::isEditMode() && $entity->userHasLock(sfContext::getInstance()->getUser()->getGuardUser()))
  {
    $html  = '<div class="sympal_editable_entity_slot" onMouseOver="javascript: highlight_entity_slot(\''.$slot['id'].'\');" onMouseOut="javascript: unhighlight_entity_slot(\''.$slot['id'].'\');" title="Double click to edit this slot named `'.$name.'`" id="edit_entity_slot_button_'.$slot['id'].'" style="cursor: pointer;" onClick="javascript: edit_entity_slot(\''.$slot['id'].'\');">';
    $html .= $entitySlot;
    $html .= '</div>';

    $editor  = '<div class="sympal_edit_slot_box yui-skin-sam">';
    $editor .= '<div id="edit_entity_slot_'.$slot['id'].'">';
    $editor .= '<div class="hd">Edit Slot: '.$slot['name'].'</div>';
    $editor .= '<div class="bd" id="edit_entity_slot_content_'.$slot['id'].'"></div>';
    $editor .= '</div>';
    $editor .= '</div>';

    $editor .= sprintf(<<<EOF
<script type="text/javascript">
myPanel = new YAHOO.widget.Panel('edit_entity_slot_%s', {
	underlay:"shadow",
	close:true,
	visible:true,
	context:['edit_entity_slot_button_%s', 'tl', 'tl'],
  autofillheight: "body",
  constraintoviewport: true,
	draggable:true} );

myPanel.cfg.setProperty("underlay", "matte");
myPanel.render();
myPanel.hide();

YAHOO.util.Event.addListener("edit_entity_slot_button_%s", "dblclick", myPanel.show, myPanel, true);
YAHOO.util.Event.addListener("edit_entity_slot_editor_panel_button_%s", "click", myPanel.show, myPanel, true);
</script>
EOF
    ,
      $slot['id'],
      $slot['id'],
      $slot['id'],
      $slot['id'],
      $slot['id'],
      $slot['id']
    );

    slot('sympal_editors', get_slot('sympal_editors').$editor);

    return $html;
  } else {
    return $entitySlot;
  }
}

function render_entity_slot($entitySlot)
{
  return $entitySlot->render();
}