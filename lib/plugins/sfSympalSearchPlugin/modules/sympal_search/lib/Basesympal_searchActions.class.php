<?php

/**
 * Base actions for the sfSympalSearchPlugin sympal_search module.
 * 
 * @package     sfSympalSearchPlugin
 * @subpackage  sympal_search
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_searchActions extends sfActions
{
  private function _executeSearch(sfWebRequest $request)
  {
    if ($q = $request->getParameter('q'))
    {
      $query = Doctrine_Core::getTable('sfSympalContent')->getSearchQuery($q);

      $this->dataGrid = sfSympalDataGrid::create($query)
        ->addColumn('a.title', 'renderer=sympal_search/title label=Search Result');

      $this->getResponse()->setTitle('Sympal Search / '.$q);
    } else {
      $this->getResponse()->setTitle('Sympal Search');
    }
  }

  public function executeAdmin_search(sfWebRequest $request)
  {
    $this->_executeSearch($request);
    $this->loadTheme('admin');
    $this->setTemplate('index');
  }

  public function executeFrontend_search(sfWebRequest $request)
  {
    $this->_executeSearch($request);
    $this->loadSiteTheme();
    $this->setTemplate('index');
  }
}