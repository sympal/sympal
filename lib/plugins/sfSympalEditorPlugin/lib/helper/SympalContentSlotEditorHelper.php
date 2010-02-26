<?php


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