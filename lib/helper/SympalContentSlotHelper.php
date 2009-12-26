<?php

/**
 * Get a Sympal Content instance property
 *
 * @param Content $content 
 * @param string $name 
 * @return mixed $value
 */
function get_sympal_content_property($content, $name)
{
  return $content->$name;
}

/**
 * Get Sympal content slot value
 *
 * @param Content $content  The Content instance
 * @param string $name The name of the slot
 * @param string $type The type of slot
 * @param string $isColumn  Whether it is a column property
 * @param string $renderFunction The function to use to render the value
 * @return void
 * @author Jonathan Wage
 */
function get_sympal_content_slot($content, $name, $type = 'Text', $isColumn = false, $renderFunction = null)
{
  if ($content->hasField($name))
  {
    $isColumn = true;
  }

  if ($isColumn && is_null($renderFunction))
  {
    $renderFunction = 'get_sympal_content_property';
  }

  $slots = $content->getSlots();

  if ($name instanceof sfSympalContentSlot)
  {
    $slot = $name;
  } else {
    $slot = $content->getOrCreateSlot($name, $type, $isColumn, $renderFunction);
  }

  $user = sfContext::getInstance()->getUser();
  if ($user->isEditMode())
  {
    return get_sympal_content_slot_editor($slot);
  } else {
    return $slot->render();
  }
}

function get_sympal_content_slot_editor(sfSympalContentSlot $slot)
{
  $name = $slot->getName();
  $isColumn = $slot->getIsColumn();

  $form = $slot->getEditForm();

  return '
<span title="Double click to edit the '.$name.' slot" id="sympal_content_slot_'.$slot->getId().'" class="sympal_content_slot">
  <input type="hidden" class="content_slot_id" value="'.$slot->getId().'" />
  <input type="hidden" class="content_id" value="'.$slot->getContentRenderedFor()->getId().'" />
  <span class="editor">'.get_partial('sympal_edit_slot/slot_editor', array('form' => $form, 'contentSlot' => $slot)).'</span>
  <span class="value toggle_edit_mode">'.$slot->render().'</span>
</span>';
}