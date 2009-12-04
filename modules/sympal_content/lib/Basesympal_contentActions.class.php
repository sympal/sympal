<?php

class Basesympal_contentActions extends autoSympal_contentActions
{
  public function preExecute()
  {
    parent::preExecute();

    if (!sfSympalToolkit::isEditMode())
    {
      $this->getUser()->setFlash('error', 'In order to work with content you must turn on edit mode!');
      $this->redirect('@homepage');
    }

    $this->useAdminTheme();
  }

  protected function _getContent(sfWebRequest $menuItem)
  {
    $q = Doctrine_Core::getTable('Content')
      ->createQuery('c')
      ->where('c.id = ?', $request->getParameter('id'));

    $content = $q->fetchOne();
    $this->forward404Unless($content);
    return $content;
  }

  protected function _publishContent(Content $content, $publish = true)
  {
    $func = $publish ? 'publish':'unpublish';
    return $content->$func();
  }

  public function executeBatchPublish(sfWebRequest $request)
  {
    $ids = $request->getParameter('ids');

    $content = Doctrine_Core::getTable('Content')
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

    $content = Doctrine_Core::getTable('Content')
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

  public function executeList_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    if (is_numeric($type))
    {
      $this->contentType = Doctrine_Core::getTable('ContentType')->find($type);
    } else {
      $this->contentType = Doctrine_Core::getTable('ContentType')->findOneBySlug($type);
    }

    $request->setAttribute('content_type', $this->contentType->name);

    $this->setTemplate('index');
    $this->executeIndex($request);
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
    $this->content = $this->getRoute()->getObject();
    $this->getUser()->checkContentSecurity($this->content);
    $this->redirect($this->content->getRoute());
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->menuItem = Doctrine_Core::getTable('MenuItem')->getForContentType('page');
    $this->contentTypes = Doctrine_Core::getTable('ContentType')->findAll();

    $this->setTemplate('new_type');
  }

  public function executeCreate_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    if (is_numeric($type))
    {
      $type = Doctrine_Core::getTable('ContentType')->find($type);      
    } else {
      $type = Doctrine_Core::getTable('ContentType')->findOneBySlug($type);
    }

    $this->menuItem = Doctrine_Core::getTable('MenuItem')->getForContentType($type['slug']);

    $this->content = new Content();
    $this->content->setType($type);
    $this->content->LockedBy = $this->getUser()->getSympalUser();
    $this->content->CreatedBy = $this->getUser()->getSympalUser();
    $this->content->site_id = sfSympalContext::getInstance()->getSite()->getId();

    Doctrine_Core::initializeModels(array($type['name']));

    $this->form = new ContentForm($this->content);
    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->content = Doctrine_Core::getTable('Content')
      ->getBaseQuery()
      ->where('c.id = ?', $request->getParameter('id'))
      ->fetchOne();

    $user = $this->getUser();
    $user->checkContentSecurity($this->content);
    $user->obtainContentLock($this->content);

    $this->form = $this->configuration->getForm($this->content);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->menuItem = Doctrine_Core::getTable('MenuItem')->getForContentType('page');

    $user = $this->getUser()->getSympalUser();

    $type = Doctrine_Core::getTable('ContentType')->find($request['content']['content_type_id']);
    $this->content = Content::createNew($type);
    $this->content->Site = sfSympalContext::getInstance()->getSite();

    $this->form = new ContentForm($this->content);

    $this->content->MenuItem->Site = $this->content->Site;

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      $this->getUser()->setFlash('notice', $form->getObject()->isNew() ? 'The item was created successfully.' : 'The item was updated successfully.');

      $new = $form->isNew();
      $content = $form->save();

      $this->getUser()->obtainContentLock($content);

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $content)));
      $this->dispatcher->notify(new sfEvent($this, 'sympal.save_content', array('content' => $content)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $this->getUser()->getFlash('notice').' You can add another one below.');

        $this->redirect('@sympal_content_new');
      }
      else
      {
        if ($new)
        {
          $this->redirect($content->getRoute());
        } else {
          $this->redirect($content->getEditRoute());
        }
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }
}