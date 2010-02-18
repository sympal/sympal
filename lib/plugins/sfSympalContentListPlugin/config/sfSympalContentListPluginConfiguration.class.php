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
  }

  public function listenToSympalLoad(sfEvent $event)
  {
    new sfSympalContentRendererFilterVariablesListener($this->dispatcher, $this);
    new sfSympalContentRendererUnknownFormatListener($this->dispatcher, $this);
  }
}