<?php

class Basesympal_contentActions extends autoSympal_contentActions
{
  public function preExecute()
  {
    parent::preExecute();
    if (!sfSympalToolkit::isEditMode())
    {
      $this->getUser()->setFlash('error', 'In order to work with content you must turn on edit mode!');
      $this->redirect('@homepage');
    }
  }

  public function executeDelete_route(sfWebRequest $request)
  {
    $this->askConfirmation('Are you sure?', 'Are you sure you wish to delete this route?');
    $this->getRoute()->getObject()->delete();

    $this->getUser()->setFlash('notice', 'Route was deleted successfully!');
    $this->redirect($request->getParameter('redirect_url'));
  }

  /**
   * Redirects to the url of the content
   * Used by admin gen shortcut buttons/actions
   */
  public function executeView()
  {
    $this->content = $this->getRoute()->getObject();
    $this->getUser()->checkContentSecurity($this->content);
    $this->redirect($this->content->getRoute());
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForContentType('page');
    $this->contentTypes = Doctrine::getTable('ContentType')->findAll();

    $this->setTemplate('new_type');
  }

  public function executeCreate_type(sfWebRequest $request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForContentType('page');

    $this->content = new Content();
    $type = Doctrine::getTable('ContentType')->findOneBySlug($request->getParameter('type'));
    $this->content->setType($type);
    $this->content->LockedBy = $this->getUser()->getGuardUser();
    $this->content->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();

    Doctrine::initializeModels(array($type['name']));

    $this->form = new ContentForm($this->content);
    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->content = $this->getRoute()->getObject();

    $user = $this->getUser();
    $user->checkContentSecurity($this->content);
    $user->obtainContentLock($this->content);

    $type = $this->content->Type;
    Doctrine::initializeModels(array($type['name']));
    $this->form = $this->configuration->getForm($this->content);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForContentType('page');

    $this->content = new Content();

    $type = Doctrine::getTable('ContentType')->find($request->getParameter('content[content_type_id]'));
    $this->content->setType($type);
    $this->content->LockedBy = $this->getUser()->getGuardUser();
    $this->content->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();

    Doctrine::initializeModels(array($type['name']));

    $this->form = new ContentForm($this->content);

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }
}