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

    $content = $actions->getRoute()->getObject();

    $actions->forward404Unless($content);
    $actions->getUser()->checkContentSecurity($content);

    $menuItem = $content->getMainMenuItem();
    $actions->forward404Unless($menuItem);

    sfSympalToolkit::changeLayout($content->getLayout());

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

  public function quickRenderContent($slug, $format = 'html')
  {
    $content = Doctrine::getTable('Content')->getContentForSite(array('slug' => $slug));
    $menuItem = $content->getMenuItem();

    $renderer = self::renderContent($menuItem, $content, $format);
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