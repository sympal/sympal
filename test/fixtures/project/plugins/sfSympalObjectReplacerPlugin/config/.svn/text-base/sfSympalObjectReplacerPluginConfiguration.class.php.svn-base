<?php

class sfSympalObjectReplacerPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_inline_edit_bar_buttons', array($this, 'loadInlineEditBarButtons'));
  }
  
  public function loadInlineEditBarButtons(sfEvent $event)
  {
    if ($event['content']->getEditableSlotsExistOnPage())
    {
      $menu = $event->getSubject();
      $menu->
        addChild('Objects', '@sympal_objects_select')->
        isEditModeButton(true)->
        setInputClass('toggle_sympal_objects')
      ;
    }
  }
}