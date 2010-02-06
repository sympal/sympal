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

function render_content_author(sfSympalContent $content, $slot)
{
  return $content->CreatedBy->username;
}

function render_content_date_published(sfSympalContent $content, $slot)
{
  use_helper('Date');
  return format_datetime($content->date_published);
}

/**
 * Get Sympal content slot value
 *
 * @param Content $content  The Content instance
 * @param string $name The name of the slot
 * @param string $type The type of slot
 * @param string $renderFunction The function/callable used to render the value of slots which are columns
 * @param array  $options Array of options for this slot
 * @return void
 */
function get_sympal_content_slot($content, $name, $type = null, $renderFunction = null, $options = array())
{
  if ($type === null)
  {
    $type = 'Text';
  }
  
  $slot = null;
  if ($name instanceof sfSympalContentSlot)
  {
    $slot = $name;
    $name = $name->getName();
  } else {
    $slot = $content->getOrCreateSlot($name, $type, $renderFunction, $options);
  }

  $slot->setContentRenderedFor($content);

  if (sfSympalContext::getInstance()->shouldLoadFrontendEditor())
  {
    use_helper('SympalContentSlotEditor');

    return get_sympal_content_slot_editor($content, $slot, $options);
  } else {
    return $slot->render();
  }
}