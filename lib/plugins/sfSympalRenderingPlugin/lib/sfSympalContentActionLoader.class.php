<?php

class sfSympalContentActionLoader
{
  protected
    $_actions,
    $_sympalContext,
    $_user,
    $_response,
    $_request,
    $_content,
    $_menuItem,
    $_dispatcher;

  public function __construct(sfActions $actions)
  {
    $this->_actions = $actions;
    $this->_sympalContext = $actions->getSympalContext();
    $this->_user = $actions->getUser();
    $this->_response = $actions->getResponse();
    $this->_request = $actions->getRequest();
    $this->_dispatcher = $this->_sympalContext->getSymfonyContext()->getConfiguration()->getEventDispatcher();
  }

  public function getContent()
  {
    if (!$this->_content)
    {
      $this->_content = $this->_actions->getRoute()->getObject();
      if ($this->_content)
      {
        $this->_sympalContext->setSite($this->_content->getSite());
        $this->_menuItem = $this->_content->getMenuItem();
      }
    }
    return $this->_content;
  }

  public function getContentRenderer()
  {
    $content = $this->loadContent();
    return $this->_sympalContext->getContentRenderer($content, $this->_request->getRequestFormat());
  }

  public function loadContent()
  {
    $content = $this->getContent();
    $this->_handleForward404($content);
    $this->_user->checkContentSecurity($content);

    $this->_loadMetaData($this->_response);

    if (!$this->_user->getCurrentTheme() || !sfSympalConfig::get('allow_changing_theme_by_url'))
    {
      $this->_sympalContext->loadTheme($content->getThemeToRenderWith());
    }

    $this->_sympalContext->setCurrentContent($content);

    // Handle custom action
    $customActionName = $content->getCustomActionName();
    if ($customActionName && $customActionName !== $this->_request->getParameter('action'))
    {
      if (method_exists($this->_actions, ($function = 'execute'.ucfirst($customActionName))))
      {
        $this->_actions->$function($this->_request);
      }

      $customTemplatePath = sfConfig::get('sf_apps_dir').'/'.sfConfig::get('sf_app').'/modules/'.$content->getModuleToRenderWith().'/templates/'.$customActionName.'Success.php';
      if (file_exists($customTemplatePath))
      {
        $this->_actions->setTemplate($customActionName);
      }
    }

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.load_content', array('content' => $content)));

    return $content;    
  }

  public function loadContentRenderer()
  {
    $renderer = $this->getContentRenderer();
    if ($renderer->getFormat() != 'html')
    {
      sfConfig::set('sf_web_debug', false);

      $format = $this->_request->getRequestFormat();
      $this->_request->setRequestFormat('html');
      $this->_actions->setLayout(false);

      if ($mimeType = $this->_request->getMimeType($format))
      {
        $this->_response->setContentType($mimeType);
      }
    }

    return $renderer;
  }

  private function _loadMetaData()
  {
    // page title
    if ($pageTitle = $this->_content->getPageTitle())
    {
      $this->_response->setTitle($pageTitle);
    } else if ($pageTitle = $this->_content->getSite()->getPageTitle()) {
      $this->_response->setTitle($pageTitle);
    } else if (sfSympalConfig::get('auto_seo', 'title')) {
      $this->_response->setTitle($this->_getAutoSeoTitle($this->_response));
    }

    // meta keywords
    if ($metaKeywords = $this->_content->getMetaKeywords())
    {
      $this->_response->addMeta('keywords', $metaKeywords);
    } else if ($metaKeywords = $this->_content->getSite()->getMetaKeywords()) {
      $this->_response->addMeta('keywords', $metaKeywords);
    }

    // meta description
    if ($metaDescription = $this->_content->getMetaDescription())
    {
      $this->_response->addMeta('description', $metaDescription);
    } else if ($metaDescription = $this->_content->getSite()->getMetaDescription()) {
      $this->_response->addMeta('description', $metaDescription);
    }
  }

  private function _getAutoSeoTitle()
  {
    if ($this->_menuItem)
    {
      $breadcrumbs = $this->_menuItem->getBreadcrumbs();
      $children = array();
      foreach ($breadcrumbs->getChildren() as $child)
      {
        $children[] = $child->renderLabel();
      }
      array_shift($children);

      $title = implode(sfSympalConfig::get('breadcrumbs_separator', null, ' / '), $children);
    } else {
      $title = (string) $this->_content;
    }
    $format = sfSympalConfig::get('auto_seo', 'title_format');
    $find = array(
      '%site_title%',
      '%content_title%',
      '%menu_item_label%',
      '%content_id%',
      '%separator%',
      '%ancestors%'
    );
    $replace = array(
      $this->_content->getSite()->getTitle(),
      (string) $this->_content,
      ($this->_menuItem ? $this->_menuItem->getLabel() : (string) $this->_content),
      $this->_content->getId(),
      sfSympalConfig::get('breadcrumbs_separator'),
      ($title ? $title : (string) $this->_content)
    );
    $title = str_replace($find, $replace, $format);
    $this->_response->setTitle($title);
    return $title;
  }

  private function _createSite()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfSympalCreateSiteTask($this->_dispatcher, new sfFormatter());
    $task->run(array($this->_sympalContext->getSiteSlug()), array('no-confirmation' => true));
  }

  private function _handleForward404($record)
  {
    if (!$record)
    {
      $site = $this->_sympalContext->getSite();

      // No site record exception
      if (!$site)
      {
        // Site doesn't exist for this application make sure the user wants to create a site for this application
        $this->_actions->askConfirmation(
          sprintf('No site found for the application named "%s"', $this->_sympalContext->getSiteSlug()),
          sprintf('Do you want to create a site for the application named "%s"? Clicking yes will create a site record in the database and allow you to begin building out the content for your site!', $this->_sympalContext->getSiteSlug())
        );

        $this->_createSite();
        $this->_actions->refresh();
      // Check for no content and redirect to default new site page
      } else {
        $q = Doctrine_Query::create()
          ->from('sfSympalContent c')
          ->andWhere('c.site_id = ?', $site->getId());
        $count = $q->count();
        if (!$count)
        {
          $this->_actions->forward('sympal_default', 'new_site');
        }
        
        $parameters = $this->_actions->getRoute()->getParameters();
        $msg = sprintf(
          'No %s record found that relates to sfSympalContent record id "%s"',
          $parameters['sympal_content_type'],
          $parameters['sympal_content_type_id']
        );
        $this->_actions->forward404($msg);
      }
    }
  }
}