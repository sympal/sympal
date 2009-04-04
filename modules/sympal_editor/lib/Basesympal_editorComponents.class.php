<?php

class Basesympal_editorComponents extends sfComponents
{
  public function executeEdit_panel()
  {
    $this->content = $this->menuItem->getContent();
  }

  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new sfFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);
  }

  public function executeTools()
  {
    $request = sfContext::getInstance()->getRequest();

    $this->lock = $this->getUser()->getOpenContentLock();

    $this->menu = new sfSympalMenuTools('Sympal Tools');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_tools', array('menu' => $this->menu, 'content' => $this->content, 'menuItem' => $this->menuItem, 'lock' => $this->lock)));
  }

  public function executeAdmin_bar()
  {
    $this->menu = new sfSympalMenuAdminBar('Sympal Admin Bar');
    $this->menu->addChild('Icon', null, array('label' => '<div id="sympal-icon">Sympal</div>'));

    $user = sfContext::getInstance()->getUser();
    $mode = $user->getAttribute('sympal_edit') ? 'off':'on';
    $currentMode = $user->getAttribute('sympal_edit') ? 'on':'off';
    $this->menu->addChild('Toggle Edit Mode', '@sympal_toggle_edit', array('label' => image_tag('/sf/sf_admin/images/edit.png').' Turn '.ucfirst($mode), 'title' => 'Click to turn '.$mode.' edit mode. Edit mode is currently '.$currentMode.'.', 'class' => $mode));

    $this->menu->addChild('Administration');
    $this->menu->addChild('Security');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_admin_bar', array('menu' => $this->menu)));
  }

  public function executeSide_bar()
  {
    $this->menu = new sfSympalMenuSideBar('Sympal Side Bar');
    $sideBar = $this->menu['Root']->setLabel('&nbsp;');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_side_bar', array('menu' => $sideBar)));
  }
}