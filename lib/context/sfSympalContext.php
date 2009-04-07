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
    return $this->_site;
  }

  public function getSiteRecord()
  {
    return Doctrine::getTable('Site')
      ->createQuery('s')
      ->where('s.slug = ?', $this->_site)
      ->fetchOne();
  }

  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  public function getRenderer(sfActions $actions)
  {
    $routeOptions = $actions->getRoute()->getOptions();
    $request = $actions->getRequest();

    if ($routeOptions['type'] == 'list')
    {
      $menuItem = Doctrine::getTable('MenuItem')->getForContentType($request->getParameter('sympal_content_type'));
      $actions->forward404Unless($menuItem);

      $pager = $actions->getRoute()->getObjects();
      $pager->setPage($request->getParameter('page', 1));
      $pager->init();

      $content = $pager->getResults();

      $renderer = $this->renderContent($menuItem, $content, $request->getRequestFormat());
      $renderer->setPager($pager);
    } else {
      $content = $actions->getRoute()->getObject();
      $actions->getUser()->checkContentSecurity($content);
      $actions->forward404Unless($content);
      $menuItem = $content->getMainMenuItem();

      $actions->forward404Unless($menuItem);

      sfSympalToolkit::changeLayout($content->getLayout());

      $actions->getUser()->obtainContentLock($content);

      $renderer = $this->renderContent($menuItem, $content, $request->getRequestFormat());
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

  public function renderContent($menuItem, $content = null, $format = 'html')
  {
    $renderer = new sfSympalContentRenderer($menuItem, $format);
    $renderer->setContent($content);
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
}