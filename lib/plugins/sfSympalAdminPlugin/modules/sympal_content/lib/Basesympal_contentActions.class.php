<?php

class Basesympal_contentActions extends autoSympal_contentActions
{
  protected function _publishContent(sfSympalContent $content, $publish = true)
  {
    $func = $publish ? 'publish':'unpublish';
    return $content->$func();
  }

  protected function addSortQuery($query)
  {
    $query->addOrderBy('m.root_id ASC, m.lft ASC');
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

  protected function _getContentType($type, sfWebRequest $request)
  {
    if (!$this->contentType)
    {
      if ($type)
      {
        if (is_numeric($type))
        {
          $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->find($type);
        } else {
          $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByNameOrSlug($type, $type);
        }
        $this->getUser()->setAttribute('content_type_id', $this->contentType->id);
        $this->getRequest()->setAttribute('content_type', $this->contentType);
      } else {
        $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->find($this->getUser()->getAttribute('content_type_id'));
      }
    }

    return $this->contentType;
  }

  public function executeFilter(sfWebRequest $request)
  {
    $this->contentType = $this->_getContentType($request->getParameter('type'), $request);
    parent::executeFilter($request);
  }

  public function executeList_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    $this->contentType = $this->_getContentType($type, $request);

    $this->setTemplate('index');
    $this->executeIndex($request);
  }

  public function executeContent_types_index(sfWebRequest $request)
  {
    $this->contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
  }

  public function executeIndex(sfWebRequest $request)
  {
    if ($request->hasParameter('published'))
    {
      $filters = $this->getFilters();
      $filters['is_published'] = $request->getParameter('published');
      $this->setFilters($filters);
      $this->redirect('@sympal_content');
    }
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

  protected function _getContentById($id)
  {
    $contentType = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('t.name')
      ->leftJoin('c.Type t')
      ->where('c.id = ?', $id)
      ->execute(array(), Doctrine_Core::HYDRATE_NONE);

    $this->sf_sympal_content = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery($contentType[0][0])
      ->where('c.id = ?', $id)
      ->fetchOne();

    $this->forward404Unless($this->sf_sympal_content);
    return $this->sf_sympal_content;
  }

  protected function _getContent(sfWebRequest $request)
  {
    return $this->_getContentById($request->getParameter('id'));
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->sf_sympal_content = $this->_getContent($request);
    $user = $this->getUser();
    $user->checkContentSecurity($this->sf_sympal_content);

    $this->getSympalContext()->setCurrentContent($this->sf_sympal_content);
    if ($menuItem = $this->sf_sympal_content->getMenuItem())
    {
      $this->getSympalContext()->setCurrentMenuItem($this->sf_sympal_content->getMenuItem());
    }

    $this->getResponse()->setTitle('Sympal Admin / Editing '.$this->sf_sympal_content);

    $this->form = $this->configuration->getForm($this->sf_sympal_content);
  }

  public function executeEdit_slots(sfWebRequest $request)
  {
    $this->sf_sympal_content = $this->_getContent($request);
    $this->getSympalContext()->setCurrentContent($this->sf_sympal_content);
    if ($menuItem = $this->sf_sympal_content->getMenuItem())
    {
      $this->getSympalContext()->setCurrentMenuItem($this->sf_sympal_content->getMenuItem());
    }
    $this->getSympalContext()->getContentRenderer($this->sf_sympal_content)->render();

    $this->dispatcher->notify(new sfEvent($this, 'sympal.load_content', array('content' => $this->sf_sympal_content)));

    $this->sf_sympal_content->refresh(true);
    $this->sf_sympal_content->populateSlotsByName();

    $this->getContext()->getConfiguration()->getPluginConfiguration('sfSympalEditorPlugin')->loadEditorAssets();
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

      $originalCustomPath = $form->getObject()->getCustomPath();
      $content = $form->save();
      $id = $content->getId();

      if ($originalCustomPath !== $form->getObject()->getCustomPath())
      {
        $this->clearCache();
      }

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
      else if ($request->hasParameter('_save_and_edit_slots'))
      {
        $this->redirect('@sympal_content_edit_slots?id='.$content->id);
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