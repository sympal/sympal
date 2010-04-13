<?php

/**
 * Get the floating sympal editor for the given MenuItem and Content instances
 *
 * @return string $html
 */
function get_sympal_editor()
{
  return get_partial('sympal_editor/editor');
}

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
