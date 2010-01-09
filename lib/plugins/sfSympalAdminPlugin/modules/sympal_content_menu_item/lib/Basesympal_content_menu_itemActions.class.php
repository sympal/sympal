<?php

/**
 * Base actions for the sfSympalPlugin sympal_content_menu_item module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_content_menu_item
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_content_menu_itemActions extends sfActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->loadAdminTheme();
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->content = $this->getRoute()->getObject();
    $this->menuItem = $this->content->getMenuItem();
    $this->menuItem->Site = $this->content->Site;

    $this->getResponse()->setTitle(sprintf('Sympal Admin / Editing the "%s" Page Menu Item', $this->content));

    $this->form = new sfSympalMenuItemForm($this->menuItem);
    $widgetSchema = $this->form->getWidgetSchema();
    $widgetSchema['parent_id']->setOption('add_empty', '');
    unset(
      $this->form['id'],
      $this->form['is_primary'],
      $this->form['content_id'],
      $this->form['groups_list'],
      $this->form['permissions_list'],
      $this->form['slug'],
      $this->form['custom_path'],
      $this->form['requires_auth'],
      $this->form['requires_no_auth']
    );

    if ($this->menuItem->isNew())
    {
      $this->form->setDefault('name', $this->content->getTitle());
      $this->form->setDefault('label', $this->content->getTitle());
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->form->save();

        $this->getUser()->setFlash('notice', 'Menu saved successfully!');
        $this->redirect('@sympal_content_edit?id='.$this->content->id);
      }
    }
  }
}