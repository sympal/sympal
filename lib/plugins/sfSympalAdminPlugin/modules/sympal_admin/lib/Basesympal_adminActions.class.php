<?php

/**
 * Base actions for the sfSympalAdminPlugin sympal_admin module.
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  sympal_admin
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_adminActions extends sfActions
{
  public function executeClear_cache(sfWebRequest $request)
  {
    $this->clearCache();
    $this->getUser()->setFlash('notice', 'Cache cleared successfully!');
    $this->redirect($this->getUser()->getReferer($request->getReferer()));
  }

  public function executeSignin($request)
  {
    $user = $this->getUser();
    if ($user->isAuthenticated())
    {
      return $this->redirect('@sympal_dashboard');
    }

    $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'sfGuardFormSignin'); 
    $this->form = new $class();

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('signin'));
      if ($this->form->isValid())
      {
        $values = $this->form->getValues(); 
        $this->getUser()->signin($values['user'], array_key_exists('remember', $values) ? $values['remember'] : false);
        return $this->redirect('@sympal_dashboard');
      }
    }
  }

  public function executeSave_nested_set(sfWebRequest $request)
  {
    $this->getContext()->getLogger()->log(var_export($request->getParameterHolder()->getAll(), true));
    $data = $request->getParameterHolder()->getAll();
    foreach ($data as $key => $value)
    {
      if (strpos($key, 'sf_admin_nested_set_') !== false)
      {
        $items = $value['items'];
        foreach ($items as $item)
        {
          if (isset($item['children']))
          {
            $this->_saveChildren($request->getParameter('model'), $item['id'], $item['children']);
          }
        }
      }
    }
    return sfView::NONE;
  }

  private function _saveChildren($model, $parentId, $children)
  {
    $menuItem = Doctrine_Core::getTable($model)->find($parentId);
    foreach ($children as $child)
    {
      $childMenuItem = Doctrine_Core::getTable($model)->find($child['id']);
      $childMenuItem->getNode()->moveAsLastChildOf($menuItem);
      $this->getContext()->getLogger()->log('test');
      if (isset($child['children']))
      {
        $this->_saveChildren($model, $child['id'], $child['children']);
      }
    }
  }

  public function executeCheck_server(sfWebRequest $request)
  {
    $this->getResponse()->setTitle('Sympal Admin / Check Server');

    $check = new sfSympalServerCheck();
    $this->renderer = new sfSympalServerCheckHtmlRenderer($check);
  }

  public function executePhpinfo(sfWebRequest $request)
  {
    $this->setLayout(false);
  }
}