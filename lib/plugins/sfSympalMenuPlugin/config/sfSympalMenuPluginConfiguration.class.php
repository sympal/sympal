<?php

/**
 * Plugin configuration for the menu plugin
 * 
 * @package     sfSympalMenuPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-01
 * @version     svn:$Id$ $Author$
 */
class sfSympalMenuPluginConfiguration extends sfPluginConfiguration
{
  protected
    $_sympalContext;

  public function initialize()
  {
    $this->dispatcher->connect('sympal.load_admin_menu', array($this, 'loadAdminMenu'));
    $this->dispatcher->connect('sympal.load', array($this, 'bootstrap'));
  }

  public function loadAdminMenu(sfEvent $event)
  {
    $event->getSubject()
      ->getChild('Site Administration')
      ->addChild('Menus', '@sympal_menu_items')->setCredentials(array('ManageMenus'));
  }

  /**
   * Listens to the sympal.load event
   */
  public function bootstrap(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    
    $event->getSubject()->getApplicationConfiguration()->loadHelpers('SympalMenu');
    
    // Listen to sfSympalContent's change_content event
    $this->dispatcher->connect('sympal.content.set_content', array(
      $event->getSubject()->getService('menu_manager'),
      'listenContentSetContent'
    ));
    
    // extend the component/action class
    $actions = new sfSympalMenuActions();
    $this->dispatcher->connect('component.method_not_found', array($actions, 'extend'));

    // Hook into the template.filter_parameters event
    $this->dispatcher->connect('template.filter_parameters', array($this, 'listenTemplateFilterParameters'));
  }

  /**
   * Listens to template.filter_parameters and adds a few variables to the view
   */
  public function listenTemplateFilterParameters(sfEvent $event, $parameters)
  {
    $parameters['sf_sympal_menu_manager'] = $this->_sympalContext->getService('menu_manager');
    
    return $parameters;
  }
}