<?php

function get_sympal_content_slot_editor(sfSympalContent $content, sfSympalContentSlot $slot, $options = array())
{
  $content->setEditableSlotsExistOnPage(true);

  $slot->setContentRenderedFor($content);

  $name = $slot->getName();
  $isColumn = $slot->getIsColumn();

  $form = $slot->getEditForm();

  return '
<span title="'.__('[Double click to enable inline edit mode.]').'" id="sympal_content_slot_'.$slot->getId().'" class="sympal_content_slot">
  <input type="hidden" class="content_slot_id" value="'.$slot->getId().'" />
  <input type="hidden" class="content_id" value="'.$slot->getContentRenderedFor()->getId().'" />
  <span class="editor">'.get_partial('sympal_edit_slot/slot_editor', array('form' => $form, 'contentSlot' => $slot)).'</span>
  <span class="value toggle_edit_mode">'.$slot->render().'</span>
</span>';
}

function get_sympal_inline_edit_bar_buttons()
{
  $sympalContext = sfSympalContext::getInstance();
  $content = $sympalContext->getCurrentContent();
  $menuItem = $sympalContext->getCurrentMenuItem();

  $menu = new sfSympalMenuInlineEditBarButtons('Inline Edit Bar Buttons');
  $menu->setUlClass('sympal_inline_edit_bar_buttons');

  $menu->
    addChild('<div class="sympal_inline_edit_loading"></div>')->
    isButton(false)
  ;

  if ($content->getEditableSlotsExistOnPage())
  {
    $menu->addChild('Links', '@sympal_editor_links')->
      isEditModeButton(true)->
      setShortcut('Ctrl+L')->
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