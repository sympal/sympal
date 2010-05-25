<?php

/**
 * Main admin actions for editing content
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class Basesympal_contentActions extends autoSympal_contentActions
{
  /**
   * Executes the filtering - adds the contentType variable
   */
  public function executeFilter(sfWebRequest $request)
  {
    $this->contentType = $this->_getContentType($request->getParameter('type'), $request);
    parent::executeFilter($request);
  }

  /**
   * Displays a list of the content types, like an index page for all content
   */
  public function executeContent_types_index(sfWebRequest $request)
  {
    $this->contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
  }

  /**
   * Displays the index list for a given content type
   */
  public function executeList_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    $this->contentType = $this->_getContentType($type, $request);

    $this->setTemplate('index');
    $this->executeIndex($request);
  }

  /**
   * The main index, list action, which adds the following:
   *   * Adds a is_published filter based on a "published" parameter
   *   * Specifies the content type based off of a user attribute or request parameter
   */
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

  /**
   * The "new" action. If a content_type_id is passed, this redirects to
   * the new action of the content_type.
   * 
   * If no content_type_id is passed, forwards to an action that presents
   * with options for a new content object
   */
  public function executeNew(sfWebRequest $request)
  {
    $contentTypeId = $this->getUser()->getAttribute('content_type_id');
    if ($contentTypeId)
    {
      $this->redirect('@sympal_content_create_type?type='.$contentTypeId);
    }
    else
    {
      $this->forward('sympal_content', 'chooseNewType');
    }
  }

  /**
   * Forwarded from new action
   * 
   * Presents a selection of content types to choose for new content
   */
  public function executeChooseNewType(sfWebRequest $request)
  {
    $this->contentTypes = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
  }

  /**
   * The actual action that handles creating a new piece of content
   * 
   * Uses the new template
   */
  public function executeCreate_type(sfWebRequest $request)
  {
    $type = $request->getParameter('type');
    $this->_getContentType($type, $request);

    $this->sf_sympal_content = new sfSympalContent();
    $this->sf_sympal_content->setType($this->contentType);
    $this->sf_sympal_content->CreatedBy = $this->getUser()->getGuardUser();

    Doctrine_Core::initializeModels(array($type['name']));

    $this->form = new sfSympalContentForm($this->sf_sympal_content);
    $this->setTemplate('new');
  }

  /**
   * Handles the create processing, which submits from create_type
   */
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

  /**
   * The normal edit action
   */
  public function executeEdit(sfWebRequest $request)
  {
    $this->sf_sympal_content = $this->_getContent($request);
    $user = $this->getUser();
    $user->checkContentSecurity($this->sf_sympal_content);

    $this->getSympalContext()->setCurrentContent($this->sf_sympal_content);

    $this->getResponse()->setTitle('Sympal Admin / Editing '.$this->sf_sympal_content);

    $this->form = $this->configuration->getForm($this->sf_sympal_content);
  }

  /**
   * Displays and allows for editing of a content record's slot
   */
  public function executeEdit_slots(sfWebRequest $request)
  {
    $this->sf_sympal_content = $this->_getContent($request);
    $this->getSympalContext()->setCurrentContent($this->sf_sympal_content);
    $this->getSympalContext()->getContentRenderer($this->sf_sympal_content)->render();

    // throw the sympal.load_content event
    $this->dispatcher->notify(new sfEvent($this, 'sympal.load_content', array('content' => $this->sf_sympal_content)));

    // refresh the content record and refresh its internal slots list
    $this->sf_sympal_content->refresh(true);
    $this->sf_sympal_content->getSlotsByName(true);

    $this->getContext()->getConfiguration()->getPluginConfiguration('sfSympalEditorPlugin')->loadEditorAssets();
  }


  /*
   * *************  Batch functions
   */


  /**
   * Publishes an array of given content ids
   * 
   * Used by the bulk actions
   */
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

  /**
   * Unpublishes an array of given content ids
   * 
   * Used by the bulk actions
   */
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

  /**
   * ************* Protected functions
   */

  
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

  /**
   * Returns and sets up the sfSympalContentType record based on the given
   * $type variable
   * 
   * @param string $type The id, name, or slug of the type
   * @param sfWebRequest $request The request object
   * 
   * @return sfSympalContentType
   */
  protected function _getContentType($type, sfWebRequest $request)
  {
    if (!$this->contentType)
    {
      if ($type)
      {
        if (is_numeric($type))
        {
          $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->find($type);
        }
        else
        {
          $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByNameOrSlug($type, $type);
        }
        $this->getUser()->setAttribute('content_type_id', $this->contentType->id);
        $this->getRequest()->setAttribute('content_type', $this->contentType);
      }
      else
      {
        $this->contentType = Doctrine_Core::getTable('sfSympalContentType')->find($this->getUser()->getAttribute('content_type_id'));
      }
    }

    return $this->contentType;
  }

  /**
   * Retrieves and initializes the sfSympalContent record defined by
   * the id parameter.
   * 
   * This will also forward 404 if no content is matched
   * 
   * @return sfSympalContent
   */
  protected function _getContent(sfWebRequest $request)
  {
    $id = $request->getParameter('id');

    // find the content type
    $contentType = Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('t.name, c.id')
      ->leftJoin('c.Type t')
      ->where('c.id = ?', $id)
      ->execute(array(), Doctrine_Core::HYDRATE_NONE);

    $this->forward404Unless($contentType, sprintf('Cannot find content type with id "%s"', $id));

    // return the full query by using that content type
    $this->sf_sympal_content = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery($contentType[0][1])
      ->where('c.id = ?', $id)
      ->fetchOne();

    $this->forward404Unless($this->sf_sympal_content);
    
    return $this->sf_sympal_content;
  }

  /**
   * Publishes or unpublish the givent content record
   */
  protected function _publishContent(sfSympalContent $content, $publish = true)
  {
    $func = $publish ? 'publish':'unpublish';
    return $content->$func();
  }

  /**
   * Adds the default sorting query
   */
  protected function addSortQuery($query)
  {
    $query->addOrderBy('m.root_id ASC, m.lft ASC');
  }
}