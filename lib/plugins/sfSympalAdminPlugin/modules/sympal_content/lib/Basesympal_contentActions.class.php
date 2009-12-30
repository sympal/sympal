<?php

class Basesympal_contentActions extends autoSympal_contentActions
{
  public function preExecute()
  {
    parent::preExecute();

    if (!$this->getUser()->isEditMode())
    {
      $this->getUser()->setFlash('error', 'In order to work with content you must turn on edit mode!');
      $this->redirect('@homepage');
    }

    $this->useAdminLayout();
  }

  protected function _getContent(sfWebRequest $menuItem)
  {
    $q = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->where('c.id = ?', $request->getParameter('id'));

    $content = $q->fetchOne();
    $this->forward404Unless($content);
    return $content;
  }

  protected function _publishContent(sfSympalMenuItemGroupContent $content, $publish = true)
  {
    $func = $publish ? 'publish':'unpublish';
    return $content->$func();
  }

  public function executeBatchPublish(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $content = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->whereIn('c.id', $ids)
      ->execute();

    foreach ($content as $content)
    {
      $this->_publishContent($content, true);
    }
  }

  public function executeBatchUnpublish(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $content = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->whereIn('c.id', $ids)
      ->execute();

    foreach ($content as $content)
    {
      $this->_publishContent($content, false);
    }
  }

  public function executePublish(sfWebRequest $request)
  {
    $content = $this->_getContent($request);
    $this->_publishMenuItem($content, true);

    $msg = $publish ? 'Content published successfully!':'Content unpublished successfully!';
    $this->getUser()->setFlash('notice', $msg);
    $this->redirect($request->getReferer());
  }

  public function executeUnpublish(sfWebRequest $request)
  {
    $content = $this->_getContent($request);
    $this->_publishContent($content, false);
  }

  protected function _getContentType($type, sfWebRequest $request)
  {
    if (!$this->contentType)
    {
      if (is_numeric($type))
      {
        $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->find($type);
      } else {
        $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByNameOrSlug($type, $type);
      }
      $this->getUser()->setAttribute('content_type_id', $this->contentType->id);
      $this->getRequest()->setAttribute('content_type', $this->contentType);
    }

    return $this->contentType;
  }

  public function executeList_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    $this->contentType = $this->_getContentType($type, $request);

    $this->setTemplate('index');
    $this->executeIndex($request);
  }

  public function executeIndex(sfWebRequest $request)
  {
    $type = $this->getUser()->getAttribute('content_type_id', sfSympalConfig::get('default_admin_list_content_type', null, 'sfSympalPage'));
    $this->contentType = $this->_getContentType($type, $request);

    $this->getResponse()->setTitle('Sympal Admin / '.$this->contentType->getLabel());

    parent::executeIndex($request);
  }

  public function executeDelete_route(sfWebRequest $request)
  {
    $this->askConfirmation('Are you sure?', 'Are you sure you wish to delete this route?');
    $this->getRoute()->getObject()->delete();

    $this->getUser()->setFlash('notice', 'Route was deleted successfully!');
    $this->redirect($request->getParameter('redirect_url'));
  }

  /**
   * Redirects to the url of the content
   * Used by admin gen shortcut buttons/actions
   */
  public function executeView()
  {
    $this->sf_sympal_content = $this->getRoute()->getObject();
    $this->getUser()->checkContentSecurity($this->sf_sympal_content);
    $this->redirect($this->sf_sympal_content->getRoute());
  }

  public function executeNew(sfWebRequest $request)
  {
    $contentTypeId = $this->getUser()->getAttribute('content_type_id');
    $this->redirect('@sympal_content_create_type?type='.$contentTypeId);
  }

  public function executeCreate_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    if (is_numeric($type))
    {
      $type = Doctrine_Core::getTable('sfSympalContentType')->find($type);      
    } else {
      $type = Doctrine_Core::getTable('sfSympalContentType')->findOneBySlug($type);
    }

    $this->sf_sympal_content = new sfSympalContent();
    $this->sf_sympal_content->setType($type);
    $this->sf_sympal_content->CreatedBy = $this->getUser()->getGuardUser();

    Doctrine_Core::initializeModels(array($type['name']));

    $this->form = new sfSympalContentForm($this->sf_sympal_content);
    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $contentType = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('t.name')
      ->leftJoin('c.Type t')
      ->where('c.id = ?', $request->getParameter('id'))
      ->execute(array(), Doctrine_Core::HYDRATE_NONE);

    $this->sf_sympal_content = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery($contentType[0][0])
      ->where('c.id = ?', $request->getParameter('id'))
      ->fetchOne();

    $user = $this->getUser();
    $user->checkContentSecurity($this->sf_sympal_content);

    $this->getResponse()->setTitle('Sympal Admin / Editing '.$this->sf_sympal_content);

    $this->form = $this->configuration->getForm($this->sf_sympal_content);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $content = $request->getParameter('sf_sympal_content');
    $contentTypeId = $content['content_type_id'];
    $type = Doctrine_Core::getTable('sfSympalContentType')->find($contentTypeId);
    $this->sf_sympal_content = sfSympalContent::createNew($type);
    $this->sf_sympal_content->Site = $this->getSympalContext()->getSite();

    $this->form = new sfSympalContentForm($this->sf_sympal_content);

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.');

      $content = $form->save();

      $this->getContext()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')
        ->getSympalConfiguration()->getCache()->resetRouteCache();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $content)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' You can add another one below.');

        $this->redirect('@sympal_content_new');
      }
      else if ($request->hasParameter('_save_and_view'))
      {
        $this->redirect($content->getRoute());
      }
      else if ($request->hasParameter('_save_and_edit_menu'))
      {
        $this->redirect('@sympal_content_menu_item?id='.$content->id);
      }
      else
      {
        $this->redirect($content->getEditRoute());
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }
}