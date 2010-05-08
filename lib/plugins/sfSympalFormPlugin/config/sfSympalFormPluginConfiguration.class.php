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

    // Connect to the sympal.load_config_form evnet
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
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

    // Register a listener on the form.filter_values event
    new sfSympalFormFilterValuesListener($this->dispatcher, $this);

    // Register a listener on the form.post_configure event
    new sfSympalFormPostConfigureListener($this->dispatcher, $this);
  }

  /**
   * Listens to the sympal.load_config_form and allows for customization
   * of the config form
   */
  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();
    $form->addSetting('form', 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting('form', 'recaptcha_private_key', 'Recaptcha Private Key');
  }

  /**
   * @return sfSympalContext
   */
  public function getSympalContext()
  {
    return $this->_sympalContext;
  }
}