<?php

/**
 * Extension of the actions class
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  actions
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalAdminActions extends sfSympalExtendClass
{

  /**
   * Enable or disable the admin editor
   */
  public function enableEditor($enable = true)
  {
    sfConfig::set('sympal.editor_menu', $enable);
  }
}