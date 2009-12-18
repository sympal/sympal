<?php

class Basesympal_editorComponents extends sfComponents
{
  public function executeEditor()
  {
    $request = sfContext::getInstance()->getRequest();

    $this->menu = new sfSympalMenuTools('Sympal Editor');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_editor', array('menu' => $this->menu, 'content' => $this->content, 'menuItem' => $this->menuItem, 'lock' => $this->lock)));
  }
}