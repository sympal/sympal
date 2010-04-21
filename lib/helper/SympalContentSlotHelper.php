<?php

/**
 * Include a content slot in your template.
 * 
 * This maintains backwards compatibility.
 * 
 * @see _get_sympal_content_slot()
 */
function get_sympal_content_slot()
{
  $arguments = func_get_args();
  
  if ($arguments[0] instanceof sfSympalContent)
  {
    return call_user_func_array('get_sympal_content_slot2', $arguments);
  }
  else
  {
    return call_user_func_array('_get_sympal_content_slot', $arguments);
  }
}

/**
 * Include a content slot in your template.
 * 
 * @see sfSympalSlotRenderer::renderSlotByName()
 */
function _get_sympal_content_slot($name, $options = array())
{
  return sfSympalContext::getInstance()->getService('slot_renderer')->renderSlotByName($name, $options);
}

/**
 * Get Sympal content slot value
 * 
 * This is the original get_sympal_content_slot() method. The current
 * get_sympal_content_slot() method will pass to this method if it
 * detects that the first argument is an instance of sfSympalContent
 *
 * @param sfSympalContent $content  The Content instance
 * @param string $name The name of the slot
 * @param string $type The type of slot
 * @param string $renderFunction This is completely deprecated - use transformers to replace
 * @param array  $options Array of options for this slot
 * @return void
 */
function get_sympal_content_slot2($content, $name, $type = null, $renderFunction = null, $options = array())
{
  $options['content'] = $content;
  $options['type'] = $type;
  
  return _get_sympal_content_slot($name, $options);
}

