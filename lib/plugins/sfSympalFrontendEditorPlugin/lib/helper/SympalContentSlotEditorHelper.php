<?php

function get_sympal_content_slot_editor(sfSympalContentSlot $slot)
{
  $name = $slot->getName();
  $isColumn = $slot->getIsColumn();

  $form = $slot->getEditForm();

  return '
<span title="Double click to enable inline edit mode" id="sympal_content_slot_'.$slot->getId().'" class="sympal_content_slot">
  <input type="hidden" class="content_slot_id" value="'.$slot->getId().'" />
  <input type="hidden" class="content_id" value="'.$slot->getContentRenderedFor()->getId().'" />
  <span class="editor">'.get_partial('sympal_edit_slot/slot_editor', array('form' => $form, 'contentSlot' => $slot)).'</span>
  <span class="value toggle_edit_mode">'.$slot->render().'</span>
</span>';
}