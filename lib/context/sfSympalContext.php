<?php

class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;

  protected
    $_site,
    $_sympalConfiguration,
    $_symfonyContext;

  public function __construct($site, sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_site = $site;
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
  }

  public function getSite()
  {
    return sfSympalToolkit::getCurrentSite();
  }

  public function getSiteSlug()
  {
    return $this->_site;
  }

  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  public function getRenderer(MenuItem $menuItem, Content $content, $format = 'html')
  {
    $renderer = new sfSympalContentRenderer($menuItem, $format);
    $renderer->setContent($content);
    $renderer->initialize();

    return $renderer;
  }

  public function getActionsRenderer(sfActions $actions)
  {
    $request = $actions->getRequest();
    $response = $actions->getResponse();

    $content = null;
    $e = null;

    try {
      $content = $actions->getRoute()->getObject();
    } catch (Exception $e) {}

    $this->_handleForward404($content, $actions, $e);
    $actions->getUser()->checkContentSecurity($content);

    $menuItem = $content->getMainMenuItem();
    $this->_handleForward404($menuItem, $actions, $e);

    sfSympalTheme::change($content->getLayout());

    $actions->getUser()->obtainContentLock($content);

    $renderer = $this->getRenderer($menuItem, $content, $request->getRequestFormat());

    $content->loadMetaData($response);

    if ($renderer->getFormat() != 'html')
    {
      sfConfig::set('sf_web_debug', false);

      $format = $request->getRequestFormat();
      $request->setRequestFormat('html');
      $actions->setLayout(false);

      if ($mimeType = $request->getMimeType($format))
      {
        $response->setContentType($mimeType);
      }
    }

    return $renderer;
  }

  protected function _handleForward404($record, sfActions $actions, Exception $e = null)
  {
    if (!$record)
    {
      $site = sfSympalToolkit::getCurrentSite();
      if (!$site)
      {
        $message = sprintf(
          'The Symfony application "%s" does not have a site record in the database. You must either run the sympal:create-site %s or the sympal:install %s task in order to get started.',
          sfConfig::get('sf_app'),
          sfConfig::get('sf_app'),
          sfConfig::get('sf_app')
        );
        throw new sfException($message);
      } else {
        $sympalContext = sfSympalContext::getInstance();
        $q = Doctrine_Query::create()
          ->from('Content c')
          ->leftJoin('c.Site s')
          ->andWhere('s.slug = ?', $sympalContext->getSiteSlug());
        $count = $q->count();
        if (!$count)
        {
          $actions->forward('sympal_default', 'new_site');
        }

        if ($e)
        {
          throw $e;
        } else {
          $actions->forward404();
        }
      }
    }
  }

  public function quickRenderContent($type, $slug, $format = 'html')
  {
    $content = Doctrine_Core::getTable('Content')
      ->getFullTypeQuery($type)
      ->andWhere('c.slug = ?', $slug)
      ->fetchOne();

    $menuItem = $content->getMainMenuItem();

    $renderer = $this->getRenderer($menuItem, $content, $format);
    $renderer->initialize();

    return $renderer;
  }

  public static function getInstance($site = null)
  {
    if (is_null($site))
    {
      return self::$_current;
    }

    if (!isset(self::$_instances[$site]))
    {
      throw new sfException($site.' instance does not exist.');
    }
    return self::$_instances[$site];
  }

  public static function createInstance($site, sfContext $symfonyContext)
  {
    $sympalConfiguration = $symfonyContext->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();

    $instance = new self($site, $sympalConfiguration, $symfonyContext);
    self::$_instances[$site] = $instance;
    self::$_current = $instance;

    return self::$_instances[$site];
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}