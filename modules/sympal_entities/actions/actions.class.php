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

  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);

    $user = $this->getUser()->getGuardUser();

    if ($this->entity->locked_by && !$this->entity->userHasLock($user))
    {
      $this->getUser()->setFlash('error', 'Entity is already locked and being edited by ' . $this->entity->LockedBy->username);
      $this->redirect($entity->getRoute());
    }

    if (!(sfSympalTools::isEditMode() && $this->entity->userHasLock($user)))
    {
      $this->entity->obtainLock($user);

      $this->getUser()->setFlash('notice', 'Entity lock obtained successfully! Be sure to release the lock when you are done editing!');
    }
  }
}