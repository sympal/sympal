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
 * Render the author of a content record
 *
 * @param sfSympalContent $content 
 * @param string $slot 
 * @return string $author
 */
function render_content_author(sfSympalContent $content, $slot)
{
  return $content->created_by_id ? $content->CreatedBy->username : 'nobody';
}

/**
 * Render the date published for a content record
 *
 * @param sfSympalContent $content 
 * @param string $slot 
 * @return string $datePublished
 */
function render_content_date_published(sfSympalContent $content, $slot)
{
  if ($content->date_published)
  {
    sfSympalToolkit::loadHelpers('Date');
    return format_datetime($content->date_published, sfSympalConfig::get('date_published_format'));
  } else {
    return '0000-00-00';
  }
}

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
 *  * render_function The function/callable used to render the value of slots which are columns
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
      '<a href="#edit_slot_form_%s" class="edit_slot_button %s">edit</a>',
      $slot->id,
      $options['edit_mode']
    );
    
    $inlineContent .= sprintf('<span class="edit_slot_content">%s</span>', $renderedValue);
    
    // render the form inline if this is in-place editing
    $form = $slot->getEditForm();
    $inlineContent .= sprintf(
      '<span class="edit_slot_form" id="edit_slot_form_%s">%s</span>',
      $slot->id,
      get_partial('sympal_edit_slot/slot_editor', array('form' => $form, 'contentSlot' => $slot))
    );
    
    return '<span class="edit_slot_wrapper">'.$inlineContent.'</span>';
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
 * @param string $renderFunction The function/callable used to render the value of slots which are columns
 * @param array  $options Array of options for this slot
 * @return void
 */
function get_sympal_content_slot2(sfSympalContent $content, $name, $type = null, $renderFunction = null, $options = array())
{
  $options['content'] = $content;
  $options['render_function'] = $renderFunction;
  $options['type'] = $type;
  
  return _get_sympal_content_slot($name, $options);
}