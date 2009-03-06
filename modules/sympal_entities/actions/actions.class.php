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
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }

  /**
   * Redirects to the url of the entity
   * Used by admin gen shortcut buttons/actions
   */
  public function executeView()
  {
    $this->redirect($this->getRoute()->getObject()->getRoute());
  }

  public function executeCreate_type(sfWebRequest $request)
  {
    $this->entity = new Entity();
    $type = Doctrine::getTable('EntityType')->findOneByName($request->getParameter('type'));
    $this->entity->setType($type);

    $this->form = new EntityForm($this->entity);
    $this->setTemplate('new');
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->entity = new Entity();

    $type = Doctrine::getTable('EntityType')->find($request->getParameter('entity[entity_type_id]'));
    $this->entity->setType($type);

    $this->form = new EntityForm($this->entity);

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }
}