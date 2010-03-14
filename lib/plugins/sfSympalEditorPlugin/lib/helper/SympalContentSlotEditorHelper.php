<?php

/**
 * Renders the inline edit menu, which consists of buttons such as the
 * assets and links buttons
 * 
 * @return string
 */
function get_sympal_inline_edit_bar_buttons()
{
  $sympalContext = sfSympalContext::getInstance();
  $content = $sympalContext->getCurrentContent();
  $menuItem = $sympalContext->getCurrentMenuItem();

  $menu = new sfSympalMenuInlineEditBarButtons('Inline Edit Bar Buttons');
  $menu->setUlClass('sympal_inline_edit_bar_buttons');

  if ($content->getEditableSlotsExistOnPage())
  {
    $menu->addChild('Links', '@sympal_editor_links')->
      isEditModeButton(true)->
      setShortcut('Ctrl+Shift+L')->
      setInputClass('toggle_sympal_links')->
      setCredentials('InsertLinks')
    ;
  }

  sfApplicationConfiguration::getActive()->getEventDispatcher()->notify(
    new sfEvent($menu, 'sympal.load_inline_edit_bar_buttons', array(
      'content' => $content,
      'menuItem' => $menuItem
    )
  ));

  return $menu->render();
}

/**
 * Returns the form tag for the form that saves a content slot
 * 
 * @param sfForm  $form The form object for this slot
 * @param sfSympalContentSlot $contentSlot The content slot that is being modified
 * @param string $editMode The edit mode (e.g. in-place) for this form
 */
function get_sympal_slot_form_tag(sfForm $form, sfSympalContentSlot $contentSlot, $editMode)
{
  $url = url_for('sympal_save_content_slot', array(
    'id' => $contentSlot->id,
    'content_id' => $contentSlot->getContentRenderedFor()->id,
  ));
  
  $options = array(
    'method' => 'post',
    'class' => $editMode,
    'id' => 'sympal_slot_form_'.$contentSlot->id,
  );
  
  return $form->renderFormTag($url, $options);
}

/**
 * Renders an edit form for a slot
 * 
 * @param sfSympalContent $content The content on which the slot should be rendered
 * @param sfSympalContentSlot $slot The slot to render in a form
 * @param array $options An options array. Available options include:
 *   * edit_mode
 * 
 */
function get_sympal_content_slot_editor($content, $slot, $options = array())
{
  $slot->setContentRenderedFor($content);
    
  // merge the default config for this slot into the given config
  $slotOptions = sfSympalConfig::get($slot->getContentRenderedFor()->Type->slug, 'content_slots', array());
  if (isset($slotOptions[$slot->name]))
  {
    $options = array_merge($slotOptions[$slot->name], $options);
  }
  
  // merge in some edit defaults
  $options = array_merge(array(
    'edit_mode' => sfSympalConfig::get('inline_editing', 'default_edit_mode'),
  ), $options);
  
  /*
   * Give the slot a default value if it's blank.
   * 
   * @todo Move this somewhere where it can be specified on a type-by-type
   * basis (e.g., if we had an "image" content slot, it might say
   * "Click to choose image"
   */
  $renderedValue = $slot->render();
  if (!$renderedValue)
  {
    $renderedValue = __('[Hover over and click edit to change.]');
  }
  
  $inlineContent = sprintf(
    '<a href="#sympal_slot_wrapper_%s .sympal_slot_form" class="sympal_slot_button %s">'.__('Edit').'</a>',
    $slot->id,
    $options['edit_mode']
  );
  
  $inlineContent .= sprintf('<span class="sympal_slot_content">%s</span>', $renderedValue);
  
  // render the form inline if this is in-place editing
  $form = $slot->getEditForm();
  $inlineContent .= sprintf(
    '<span class="sympal_slot_form">%s</span>',
    get_partial('sympal_edit_slot/slot_editor', array(
      'form' => $form,
      'contentSlot' => $slot,
      'editMode' => $options['edit_mode'],
    ))
  );
  
  return sprintf(
    '<span class="sympal_slot_wrapper" id="sympal_slot_wrapper_%s">%s</span>',
    $slot->id,
    $inlineContent
  );
}