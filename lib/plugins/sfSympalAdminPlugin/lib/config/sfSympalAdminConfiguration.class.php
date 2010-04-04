<?php

/**
 * Extension of sfSympalConfiguration for admin tasks
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalAdminConfiguration extends sfSympalExtendClass
{
  /**
   * Check if we are inside an admin module
   * 
   * @return boolean
   */
  public function isAdminModule()
  {
    if (!$this->_symfonyContext)
    {
      return false;
    }
    $module = $this->getSympalContext()->getSymfonyContext()->getRequest()->getParameter('module');
    $adminModules = sfSympalConfig::get('admin_modules');

    return array_key_exists($module, $adminModules);
  }
}