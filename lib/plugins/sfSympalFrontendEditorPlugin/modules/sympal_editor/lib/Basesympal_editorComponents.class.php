<?php

class Basesympal_editorComponents extends sfComponents
{
  public function executeEditor()
  {
    $request = sfContext::getInstance()->getRequest();

    $this->menu = new sfSympalMenuTools('Sympal Editor');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this->menu, 'sympal.load_editor', array('content' => $this->content, 'menuItem' => $this->menuItem)));
  }
}