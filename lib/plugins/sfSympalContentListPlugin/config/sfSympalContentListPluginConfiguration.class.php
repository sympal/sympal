<?php

/**
 * sfSympalContentListPlugin configuration.
 * 
 * @package     sfSympalContentListPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class sfSympalContentListPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $this->dispatcher->connect('sympal.load', array($this, 'listenToSympalLoad'));

    // Connect to the sympal.load_config_form evnet
    $this->dispatcher->connect('sympal.load_config_form', array($this, 'loadConfigForm'));
  }

  public function listenToSympalLoad(sfEvent $event)
  {
    new sfSympalContentRendererFilterVariablesListener($this->dispatcher, $this);
    new sfSympalContentRendererUnknownFormatListener($this->dispatcher, $this);
  }

  /**
   * Listens to the sympal.load_config_form and allows for customization
   * of the config form
   */
  public function loadConfigForm(sfEvent $event)
  {
    $form = $event->getSubject();
    $form->addSetting('content_list', 'rows_per_page', 'Rows Per Page');
  }
}