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
 * This replaces get_sympal_content_slot() and is intended to be easier
 * to use. This also taps into the app.yml config for its options
 * 
 * @param string $name The name of the slot
 * @param array  $options An array of options for this slot
 * 
 * Available options include
 *  * content         An sfSympalContent instance to render the slot for
 *  * type            The rendering type to use for this slot (e.g. Markdown)
 *  * default_value   A default value to give this slot the first time it's created
 *  * edit_mode       How to edit this slot (in-place (default), popup)
 */
function _get_sympal_content_slot($name, $options = array())
{
  if (isset($options['content']))
  {
    $content = $options['content'];
    unset($options['content']);
  }
  else
  {
    $content = sfSympalContext::getInstance()->getCurrentContent();
  }
  
  // mark this content record as having content slots
  $content->setEditableSlotsExistOnPage(true);
  
  // merge the default config for this slot into the given config
  $slotOptions = sfSympalConfig::get($content->Type->slug, 'content_slots', array());
  if (isset($slotOptions[$name]))
  {
    $options = array_merge($slotOptions[$name], $options);
  }
  
  // retrieve the slot
  if ($name instanceof sfSympalContentSlot)
  {
    $slot = $name;
    $name = $name->getName();
  } else {
    $slot = $content->getOrCreateSlot($name, $options);
  }
  
  $slot->setContentRenderedFor($content);
  
  /**
   * Either render the raw value or the editor for the slot
   */
  if (sfSympalContext::getInstance()->shouldLoadFrontendEditor())
  {
    // merge in some edit defaults
    $options = array_merge(array(
      'edit_mode' => 'in-place',
    ), $options);
    
    /*
     * Give the slot a default value if it's blank.
     * 
     * @todo Move this somewhere where it can be specified on a type-by-type
     * basis (e.g., if we had an "image" content slot, it might say
     * "Click to choose image"
     */
    $renderedValue = $slot->render();
    if (!$renderedValue && sfSympalContext::getInstance()->shouldLoadFrontendEditor())
    {
      $renderedValue = __('[Hover over and click edit to change.]');
    }
    
    $inlineContent = sprintf(
      '<a href="#sympal_slot_wrapper_%s .sympal_slot_form" class="sympal_slot_button %s">edit</a>',
      $slot->id,
      $options['edit_mode']
    );
    
    $inlineContent .= sprintf('<span class="sympal_slot_content">%s</span>', $renderedValue);
    
    // render the form inline if this is in-place editing
    $form = $slot->getEditForm();
    $inlineContent .= sprintf(
      '<span class="sympal_slot_form">%s</span>',
      get_partial('sympal_edit_slot/slot_editor', array('form' => $form, 'contentSlot' => $slot, 'options' => $options))
    );
    
    return sprintf(
      '<span class="sympal_slot_wrapper" id="sympal_slot_wrapper_%s">%s</span>',
      $slot->id,
      $inlineContent
    );
  } else {
    return $slot->render();
  }
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
function get_sympal_content_slot2(sfSympalContent $content, $name, $type = null, $renderFunction = null, $options = array())
{
  $options['content'] = $content;
  $options['type'] = $type;
  
  return _get_sympal_content_slot($name, $options);
}