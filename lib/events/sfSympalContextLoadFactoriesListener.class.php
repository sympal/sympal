<?php

/**
 * Handles all the sympal functionality that needs to fire on the context.load_factories event
 * 
 * @deprecated
 * This class has been replaced by sfSympalContext.
 * 
 * @package     sfSympalPlugin
 * @subpackage  listener
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalContextLoadFactoriesListener extends sfSympalListener
{
  /**
   * @see sfSympalListener
   */
  public function getEventName()
  {
    return 'context.load_factories';
  }

  /**
   * The callback on the context.load_factories event.
   * 
   * The subject is sfContext and the invoker is sfSympalConfiguration
   */
  public function run(sfEvent $event)
  {
    /**
     * @TODO Reimplement in the correct location
    $this->_checkInstalled();
    */
  }

  /**
   * Check if Sympal is installed and redirect to installer if not.
   * Do some other install checks as well.
   *
   * @return void
   */
  private function _checkInstalled()
  {
    $request = $this->_symfonyContext->getRequest();

    // Prepare the symfony application if it has not been prepared yet
    if (!$this->_symfonyContext->getUser() instanceof sfSympalUser)
    {
      chdir(sfConfig::get('sf_root_dir'));
      $task = new sfSympalEnableForAppTask($this->_dispatcher, new sfFormatter());
      $task->run(array($this->_invoker->getProjectConfiguration()->getApplication()), array());

      $this->_symfonyContext->getController()->redirect('@homepage');
    }

    /*
     * Redirect to install module if...
     *   * not in test environment
     *   * sympal has not been installed
     *   * module is not already sympal_install
     */
    if (sfConfig::get('sf_environment') != 'test'
        && !sfSympalConfig::get('installed')
        && $request->getParameter('module') != 'sympal_install')
    {
      $this->_symfonyContext->getController()->redirect('@sympal_install');
    }

    /*
     * Redirect to homepage if no site record exists so we can prompt the
     * user to create a site record for this application.
     * 
     * This check is only ran in dev mode
     */
    if (sfConfig::get('sf_environment') == 'dev'
        && !$this->_sympalContext->getSite()
        && $this->_symfonyContext->getRequest()->getPathInfo() != '/')
    {
      $this->_symfonyContext->getController()->redirect('@homepage');
    }
  }
}