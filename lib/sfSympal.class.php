<?php
final class sfSympal
{
  const VERSION = '0.1.0-ALPHA';

  protected static $_instance;

  public static function getInstance()
  {
    if (!self::$_instance)
    {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public static function quickRenderEntity($slug, $format = 'html')
  {
    $entity = Doctrine::getTable('Entity')->getEntityForSite(array('slug' => $slug));
    $menuItem = $entity->getMenuItem();

    $renderer = self::renderEntity($menuItem, $entity, $format);
    $renderer->initialize();

    return $renderer;
  }

  public static function renderEntity($menuItem, Entity $entity = null, $format = 'html')
  {
    $renderer = new sfSympalEntityRenderer($menuItem, $format);
    $renderer->setEntity($entity);
    $renderer->initialize();

    return $renderer;
  }

  public static function renderEntities(MenuItem $menuItem, Doctrine_Collection $entities, $format = 'html')
  {
    $renderer = new sfSympalEntityRenderer($menuItem, $format);
    $renderer->setEntities($entities);
    $renderer->initialize();

    return $renderer;
  }
}