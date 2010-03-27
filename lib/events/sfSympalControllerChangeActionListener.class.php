<?php

/**
 * Listener class for controller.change_action
 * 
 * @package     sfSympalPlugin
 * @subpackage  events
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalControllerChangeActionListener extends sfSympalListener
{
  private
    $_checkedOnline = false;

  public function getEventName()
  {
    return 'controller.change_action';
  }

  public function run(sfEvent $event)
  {
    $this->_invoker->initializeTheme();
    $this->_checkOnlineConfiguration();
  }

  /**
   * Check if Sympal is not online and act accordingly
   *
   * @return void
   */
  private function _checkOnlineConfiguration()
  {
    if (sfSympalConfig::get('offline', 'enabled', false) && !$this->_checkedOnline)
    {
      $this->_checkedOnline = true;
      $this->_invoker->getSymfonyContext()->getController()->forward(
        sfSympalConfig::get('offline', 'module'),
        sfSympalConfig::get('offline', 'action')
      );
      throw new sfStopException();
    }
  }
}