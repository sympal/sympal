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
 */
function get_sympal_slot_form_tag(sfForm $form, sfSympalContentSlot $contentSlot)
{
  $url = url_for('sympal_save_content_slot', array(
    'id' => $contentSlot->id,
    'content_id' => $contentSlot->getContentRenderedFor()->id,
  ));
  
  $options = array(
    'method'  => 'post',
    'id'      => 'sympal_slot_form_'.$contentSlot->id,
    'class'   => 'sympal_slot_form',
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

  // merge in some global default slot options
  $options = array_merge(array(
    'edit_mode' => sfSympalConfig::get('inline_editing', 'default_edit_mode'),
    'view_url'  => url_for('sympal_content_slot_view', array('id' => $slot->id, 'content_id' => $slot->getContentRenderedFor()->id)),
  ), $options);
  
  // merge the default config for this slot into the given config
  $slotOptions = sfSympalConfig::get($slot->getContentRenderedFor()->Type->name, 'content_slots', array());
  if (isset($slotOptions[$slot->name]))
  {
    $options = array_merge($slotOptions[$slot->name], $options);
  }
  
  /*
   * Finally, override the "type" option, it should be set to whatever's
   * in the database, regardless of what the original slot options were
   */
  $options['type'] = $slot->type;
  
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
    '<a href="%s" class="sympal_slot_button">'.__('Edit').'</a>',
    url_for('sympal_content_slot_form', array('id' => $slot->id, 'content_id' => $slot->getContentRenderedFor()->id))
  );
  
  $inlineContent .= sprintf('<span class="sympal_slot_content">%s</span>', $renderedValue);
  
  return sprintf(
    '<span class="sympal_slot_wrapper %s" id="sympal_slot_wrapper_%s">%s</span>',
    htmlentities(json_encode($options)),
    $slot->id,
    $inlineContent
  );
}