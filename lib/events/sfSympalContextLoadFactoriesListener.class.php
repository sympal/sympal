<?php

/**
 * Handles all the sympal functionality that needs to fire on the context.load_factories event
 * 
 * @package     sfSympalPlugin
 * @subpackage  listener
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalContextLoadFactoriesListener extends sfSympalListener
{
  private
    $_symfonyContext,
    $_sympalContext;

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
   * The subject is sfContext and the invoker (in normal cases) will
   * be sfSympalConfiguration
   */
  public function run(sfEvent $event)
  {
    $this->_initiateUserTable();

    $this->_symfonyContext = $event->getSubject();
    $this->_invoker->setCache(new sfSympalCache($this->_invoker));
    $this->_invoker->setSymfonyContext($this->_symfonyContext);

    $this->_sympalContext = sfSympalContext::createInstance($this->_symfonyContext, $this->_invoker);
    $this->_invoker->setSympalContext($this->_sympalContext);

    $this->_enableModules();
    $this->_checkInstalled();

    $this->_invoker->initializeTheme();

    $helpers = array(
      'Sympal',
      'SympalContentSlot',
      'SympalMenu',
      'SympalPager',
      'I18N',
      'Asset',
      'Url',
      'Partial'
    );

    if ($this->_invoker->isAdminModule())
    {
      sfConfig::set('sf_login_module', 'sympal_admin');
      $helpers[] = 'Admin';
    }

    $this->_invoker->getProjectConfiguration()->loadHelpers($helpers);

    $this->_dispatcher->notify(new sfEvent($this, 'sympal.load'));

    new sfSympalContextLoadFactoriesListener($this->_dispatcher, $this->_invoker);
    new sfSympalComponentMethodNotFoundListener($this->_dispatcher, $this->_invoker);
    new sfSympalControllerChangeActionListener($this->_dispatcher, $this->_invoker);
    new sfSympalTemplateFilterParametersListener($this->_dispatcher, $this->_invoker);
    new sfSympalFormMethodNotFoundListener($this->_dispatcher, $this->_invoker);
    new sfSympalFormPostConfigureListener($this->_dispatcher, $this->_invoker);
    new sfSympalFormFilterValuesListener($this->_dispatcher, $this->_invoker);
  }
  
  /**
   * Initiates the user model and throws the sympal.user.set_table_definition event.
   * 
   * Ths idea is that the user model hasn't been loaded yet, so it'll be
   * loaded here for the first time, and this allows a hook into its
   * table definition.
   */
  protected _initiateUserTable()
  {
    $record = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->getRecordInstance();
    $this->_dispatcher->notify(new sfEvent($record, 'sympal.user.set_table_definition', array('object' => $record)));
  }

  /**
   * Handle the enabling of modules. Either enables all modules or only the configured modules.
   *
   * @return void
   */
  private function _enableModules()
  {
    if (sfSympalConfig::get('enable_all_modules', null, true))
    {
      $modules = sfConfig::get('sf_enabled_modules', array());
      if (sfSympalConfig::get('enable_all_modules'))
      {
        $modules = array_merge($modules, $this->_invoker->getCache()->getModules());
      } else {
        $modules = array_merge($modules, sfSympalConfig::get('enabled_modules', null, array()));
      }

      if ($disabledModules = sfSympalConfig::get('disabled_modules', null, array()))
      {
        $modules = array_diff($modules, $disabledModules);
      }

      sfConfig::set('sf_enabled_modules', $modules);
    }
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

    // Prepare the symfony application is it has not been prepared yet
    if (!$this->_symfonyContext->getUser() instanceof sfSympalUser)
    {
      chdir(sfConfig::get('sf_root_dir'));
      $task = new sfSympalEnableForAppTask($this->_dispatcher, new sfFormatter());
      $task->run(array($this->_invoker->getProjectConfiguration()->getApplication()), array());

      $this->_symfonyContext->getController()->redirect('@homepage');
    }

    // Redirect to install module if...
    //  not in test environment
    //  sympal has not been installed
    //  module is not already sympal_install
    if (sfConfig::get('sf_environment') != 'test' && !sfSympalConfig::get('installed') && $request->getParameter('module') != 'sympal_install')
    {
      $this->_symfonyContext->getController()->redirect('@sympal_install');
    }

    // Redirect to homepage if no site record exists so we can prompt the user to create
    // a site record for this application
    // This check is only ran in dev mode
    if (sfConfig::get('sf_environment') == 'dev' && !$this->_sympalContext->getSite() && $this->_symfonyContext->getRequest()->getPathInfo() != '/')
    {
      $this->_symfonyContext->getController()->redirect('@homepage');
    }
  }
}