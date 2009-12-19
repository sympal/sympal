<?php

class Basesympal_sitesActions extends autosympal_sitesActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminLayout();
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.');

      $new = !$form->getObject()->exists();

      $site = $form->save();

      if ($new)
      {
        chdir(sfConfig::get('sf_root_dir'));
        $task = new sfSympalCreateSiteTask($this->getContext()->getEventDispatcher(), new sfFormatter());
        $task->run(array($site->title, $site->description));

        $site = Doctrine_Core::getTable('sfSympalSite')->findOneByTitle($site->title);
      }

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $site)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' You can add another one below.');

        $this->redirect('@sympal_sites_new');
      }
      else if ($request->hasParameter('_save_and_list'))
      {
        $this->redirect('@sympal_sites');
      }
      else
      {
        $this->redirect('@sympal_sites_edit?id='.$site->getId());
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

    $site = $this->getRoute()->getObject();
    $this->_deleteSite($site);

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('@sympal_sites');
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $sites = Doctrine_Query::create()
      ->from('sfSympalSite')
      ->whereIn('id', $ids)
      ->execute();

    if ($sites >= count($ids))
    {
      foreach ($sites as $site)
      {
        $this->_deleteSite($site);
      }
      $this->getUser()->setFlash('notice', 'The selected items have been deleted successfully.');
    }
    else
    {
      $this->getUser()->setFlash('error', 'A problem occurs when deleting the selected items.');
    }

    $this->redirect('@sympal_sites');
  }

  protected function _deleteSite(sfSympalSite $site)
  {
    if ($site === $this->getSympalContext()->getSite())
    {
      $this->getUser()->setFlash('error', 'You cannot delete the site you are currently in!');
      $this->redirect('@sympal_sites');
    }
    $site->deleteSiteAndApplication();
  }
}