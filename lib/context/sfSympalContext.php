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
      $menuItem = Doctrine::getTable('MenuItem')->getForEntityType($request->getParameter('type'));
      $actions->forward404Unless($menuItem);

      $pager = $actions->getRoute()->getObjects();      
      $pager->setPage($request->getParameter('page', 1));
      $pager->init();

      $entities = $pager->getResults();

      $renderer = $this->renderEntities($menuItem, $entities, $request->getRequestFormat());
      $renderer->setPager($pager);
    } else {
      $entity = $actions->getRoute()->getObject();
      $actions->getUser()->checkEntitySecurity($entity);
      $actions->forward404Unless($entity);
      $menuItem = $entity->getMainMenuItem();

      $actions->forward404Unless($menuItem);

      sfSympalTools::changeLayout($entity->getLayout());

      $renderer = $this->renderEntity($menuItem, $entity, $request->getRequestFormat());
    }

    return $renderer;
  }

  public function quickRenderEntity($slug, $format = 'html')
  {
    $entity = Doctrine::getTable('Entity')->getEntityForSite(array('slug' => $slug));
    $menuItem = $entity->getMenuItem();

    $renderer = self::renderEntity($menuItem, $entity, $format);
    $renderer->initialize();

    return $renderer;
  }

  public function renderEntity($menuItem, Entity $entity = null, $format = 'html')
  {
    $renderer = new sfSympalEntityRenderer($menuItem, $format);
    $renderer->setEntity($entity);
    $renderer->initialize();

    return $renderer;
  }

  public function renderEntities(MenuItem $menuItem, Doctrine_Collection $entities, $format = 'html')
  {
    $renderer = new sfSympalEntityRenderer($menuItem, $format);
    $renderer->setEntities($entities);
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