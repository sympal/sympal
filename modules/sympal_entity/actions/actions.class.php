<?php

/**
 * sympal_entity actions.
 *
 * @package    sympal
 * @subpackage sympal_entity
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z jwage $
 */
class sympal_entityActions extends sfActions
{
  public function preExecute()
  {
    sfSympalConfig::set('use_query_caching', true);
    sfSympalConfig::set('use_result_caching', true);

    $this->setTemplate('index');
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->menuItem = Doctrine::getTable('MenuItem')->getForEntityType($request->getParameter('type'));
    $this->forward404Unless($this->menuItem);

    $this->pager = $this->getRoute()->getObjects();
    $this->pager->setPage($request->getParameter('page', 1));
    $this->pager->init();

    $this->entities = $this->pager->getResults();

    $this->renderer = sfSympal::renderEntities($this->menuItem, $this->entities, $request->getRequestFormat());
    $this->renderer->setPager($this->pager);
  }

  public function executeView(sfWebRequest $request)
  {
    $this->entity = $this->getRoute()->getObject();
    $this->forward404Unless($this->entity);
    $this->menuItem = $this->entity->getMainMenuItem();

    $this->forward404Unless($this->menuItem);

    sfSympalTools::changeLayout($this->entity->getLayout());

    $this->renderer = sfSympal::renderEntity($this->menuItem, $this->entity, $request->getRequestFormat());
  }
}