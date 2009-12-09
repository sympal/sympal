<?php

class Basesympal_editorComponents extends sfComponents
{
  public function executeLanguage(sfWebRequest $request)
  {
    $this->form = new BaseFormLanguage($this->getUser(), array('languages' => sfSympalConfig::get('language_codes', null, array($this->getUser()->getCulture()))));
    unset($this->form[$this->form->getCSRFFieldName()]);

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_change_language_form', array('form' => $this->form)));
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
    $this->menu->setCredentials(array('ViewAdminBar'));

    $this->menu->addChild('Icon', null, array('label' => '<div id="sympal-icon">Sympal</div>'));
    $this->menu->addChild('Site', '@homepage');
    $this->menu->addChild('Dashboard', '@sympal_dashboard')
      ->setCredentials(array('ViewDashboard'));

    $this->menu->addChild('Administration');
    $this->menu->addChild('Security');

    $this->getContext()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.load_admin_bar', array('menu' => $this->menu)));
  }
}