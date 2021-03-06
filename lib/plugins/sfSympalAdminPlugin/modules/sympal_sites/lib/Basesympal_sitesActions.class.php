<?php

/**
 * Actions class for handling sites
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class Basesympal_sitesActions extends autosympal_sitesActions
{
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
        $dispatcher = $this->getContext()->getEventDispatcher();
        $formatter = new sfFormatter();

        chdir(sfConfig::get('sf_root_dir'));
        $task = new sfGenerateAppTask($dispatcher, $formatter);
        $task->run(array($site->slug));
        $task = new sfSympalEnableForAppTask($dispatcher, $formatter);
        $task->run(array($site->slug));
        $task = new sfSympalCreateSiteTask($dispatcher, $formatter);
        $task->run(array($site->slug), array('no-confirmation'));

        $site = Doctrine_Core::getTable('sfSympalSite')->findOneByTitle($site->title);
      }

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $site)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' You can add another one below.');

        $this->redirect('@sympal_sites_new');
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

  /**
   * Delete site, all content within it and associated application.
   *
   * @param sfWebRequest $request
   */
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $site = $this->getRoute()->getObject();

    $this->dispatcher->notify(new sfEvent(
      $this,
      'admin.delete_object',
      array('object' => $site)
    ));

    if ($site === $this->getSympalContext()->getSite())
    {
      $this->getUser()->setFlash('error', 'You cannot delete the site you are currently in!');
    }
    else
    {
      $site->delete();
      $site->deleteApplication();
      
      $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
    }

    $this->redirect('@sympal_sites');
  }

  protected function executeBatchDelete(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $sites = Doctrine_Query::create()
      ->from('sfSympalSite')
      ->whereIn('id', $ids)
      ->execute();

    if (count($sites) >= count($ids))
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

}