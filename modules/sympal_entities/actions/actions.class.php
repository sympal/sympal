<?php

require_once dirname(__FILE__).'/../lib/sympal_entitiesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_entitiesGeneratorHelper.class.php';

/**
 * sympal_entities actions.
 *
 * @package    sympal
 * @subpackage sympal_entities
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_entitiesActions extends autoSympal_entitiesActions
{
  public function preExecute()
  {
    parent::preExecute();
    if (!sfSympalTools::isEditMode())
    {
      $this->getUser()->setFlash('error', 'In order to work with entities you must turn on edit mode!');
      $this->redirect('@homepage');
    }
  }

  /**
   * Redirects to the url of the entity
   * Used by admin gen shortcut buttons/actions
   */
  public function executeView()
  {
    $this->entity = $this->getRoute()->getObject();
    $this->getUser()->checkEntitySecurity($this->entity);
    $this->redirect($this->entity->getRoute());
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->setTemplate('new_type');
    $this->entityTypes = Doctrine::getTable('EntityType')->findAll();
  }

  public function executeCreate_type(sfWebRequest $request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForEntityType('page');

    $this->entity = new Entity();
    $type = Doctrine::getTable('EntityType')->findOneBySlug($request->getParameter('type'));
    $this->entity->setType($type);
    $this->entity->LockedBy = $this->getUser()->getGuardUser();
    $this->entity->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();

    Doctrine::initializeModels(array($type['name']));

    $this->form = new EntityForm($this->entity);
    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->entity = $this->getRoute()->getObject();
    $this->getUser()->checkEntitySecurity($this->entity);

    $type = $this->entity->Type;
    Doctrine::initializeModels(array($type['name']));
    $this->form = $this->configuration->getForm($this->entity);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->entity = new Entity();

    $type = Doctrine::getTable('EntityType')->find($request->getParameter('entity[entity_type_id]'));
    $this->entity->setType($type);
    $this->entity->LockedBy = $this->getUser()->getGuardUser();
    $this->entity->site_id = sfSympalContext::getInstance()->getSiteRecord()->getId();

    Doctrine::initializeModels(array($type['name']));

    $this->form = new EntityForm($this->entity);

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }
}