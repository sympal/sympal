<?php

class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;

  protected
    $_name,
    $_sympalConfiguration,
    $_symfonyContext;
  
  public function __construct($name, sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_name = $name;
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
  }

  public function getName()
  {
    return $this->_name;
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

  public static function getInstance($name = null)
  {
    if (is_null($name))
    {
      return self::$_current;
    }

    if (!isset(self::$_instances[$name]))
    {
      throw new sfException($name.' instance does not exist.');
    }
    return self::$_instances[$name];
  }

  public static function createInstance($name, sfContext $symfonyContext)
  {
    $sympalConfiguration = $symfonyContext->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();
    self::$_instances[$name] = new self($name, $sympalConfiguration, $symfonyContext);

    return self::$_instances[$name];
  }
}