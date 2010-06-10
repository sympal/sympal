<?php

/**
 * Plugin configuration for the form plugin
 * 
 * @package     sfSympalFormPlugin
 * @subpackage  form
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalFormPluginConfiguration extends sfPluginConfiguration
{
  protected $_dependencies = array(
    'sfSympalPlugin',
    'sfFormExtraPlugin',
  );

  protected $_sympalContext;
  
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load', array($this, 'bootstrap'));
  }

  /**
   * Boostraps the plugin
   * 
   * Listens to the sympal.load event
   */
  public function bootstrap(sfEvent $event)
  {
    $this->_sympalContext = $event->getSubject();
    
    // extend the form class
    $form = $this->_sympalContext->getServiceContainer()->getService('form_extended');
    $this->dispatcher->connect('form.method_not_found', array($form, 'extend'));

    // Register a listener on the form.post_configure event
    new sfSympalFormPostConfigureListener($this->dispatcher, $this);
  }

  /**
   * @return sfSympalContext
   */
  public function getSympalContext()
  {
    return $this->_sympalContext;
  }
}