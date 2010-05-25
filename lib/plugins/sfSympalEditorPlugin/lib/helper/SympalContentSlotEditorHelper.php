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

/**
 * Triggers a flash drop-down based on the flashes in the user
 * 
 * @param sfUser $user 
 */
function trigger_flash_from_user($user)
{
  $validTypes = array('info', 'saved', 'error');
  
  foreach ($validTypes as $validType)
  {
    if ($user->hasFlash($validType))
    {
      return sprintf('
        <script type="text/javascript">
          $(document).ready(function(){
            $.showFlashMessage("%s", "%s");
          });
        </script>', $validType, addslashes($user->getFlash($validType))
      );
    }
  }
}

/**
 * Renders the actual form that edits a content slot
 * 
 * @param sfSympalContent $content The content record being modified
 * @param sfSympalContentSlot $slot The content slot to edit
 * 
 * @return string
 */
function get_sympal_content_slot_form($content, $slot)
{
  $slot->setContentRenderedFor($content);
  $form = $slot->getEditForm();
  
  return get_partial('sympal_edit_slot/slot_editor', array('form' => $form, 'contentSlot' => $slot));
}