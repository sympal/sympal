<?php

require_once dirname(__FILE__).'/../lib/sympal_contentGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_contentGeneratorHelper.class.php';

/**
 * sympal_content actions.
 *
 * @package    sympal
 * @subpackage sympal_content
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_contentActions extends autoSympal_contentActions
{
  public function preExecute()
  {
    parent::preExecute();
    if (!sfSympalTools::isEditMode())
    {
      $this->getUser()->setFlash('error', 'In order to work with content you must turn on edit mode!');
      $this->redirect('@homepage');
    }
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
    $this->setTemplate('new_type');
    $this->contentTypes = Doctrine::getTable('ContentType')->findAll();
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
    $this->getUser()->checkContentSecurity($this->content);

    $type = $this->content->Type;
    Doctrine::initializeModels(array($type['name']));
    $this->form = $this->configuration->getForm($this->content);
  }

  public function executeCreate(sfWebRequest $request)
  {
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