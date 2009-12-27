<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '0.7.0';

  public static
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPluginManagerPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin',
      'sfSympalDataGridPlugin',
      'sfSympalUserPlugin',
      'sfSympalInstallPlugin',
      'sfSympalUpgradePlugin',
      'sfSympalRenderingPlugin',
      'sfSympalAdminPlugin',
      'sfSympalFrontendEditorPlugin'
    );

  public
    $sympalConfiguration;

  /**
   * Check Whether or not Sympal is enabled for an application
   *
   * @return boolean $enabled
   */
  public static function isSympalEnabled($application = null)
  {
    if (!$application)
    {
      $application = sfConfig::get('sf_app');
    }
    if ($application)
    {
      $reflection = new ReflectionClass($application.'Configuration');
      if ($reflection->getConstant('disableSympal'))
      {
        return false;
      }
    }
    return true;
  }

  /**
   * Enable only the Sympal plugins required for the frontend.
   *
   * @param ProjectConfiguration $configuration
   */
  public static function enableFrontendPlugins(ProjectConfiguration $configuration)
  {
    if (!self::isSympalEnabled())
    {
      return false;
    }

    $configuration->enablePlugins('sfDoctrinePlugin');
    $configuration->enablePlugins('sfSympalPlugin');
    $configuration->setPluginPath('sfSympalPlugin', dirname(dirname(__FILE__)));

    self::enableSympalCorePlugins($configuration, array(
      'sfDoctrineGuardPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfSympalMenuPlugin',
      'sfSympalPagesPlugin',
      'sfSympalContentListPlugin',
      'sfSympalDataGridPlugin',
      'sfSympalUserPlugin',
      'sfSympalRenderingPlugin',
    ));
  }

  /**
   * Enable all Sympal plugins always
   *
   * @param ProjectConfiguration $configuration 
   * @return void
   */
  public static function enableSympalPlugins(ProjectConfiguration $configuration)
  {
    if (!self::isSympalEnabled())
    {
      return false;
    }

    $configuration->enablePlugins('sfDoctrinePlugin');
    $configuration->enablePlugins('sfSympalPlugin');
    $configuration->setPluginPath('sfSympalPlugin', dirname(dirname(__FILE__)));

    self::enableSympalCorePlugins($configuration, self::$dependencies);
  }

  /**
   * Enable some Sympal core plugins
   *
   * @param ProjectConfiguration $configuration 
   * @param array|string $plugins
   * @return void
   */
  public static function enableSympalCorePlugins(ProjectConfiguration $configuration, $plugins)
  {
    foreach ((array) $plugins as $plugin)
    {
      $configuration->enablePlugins($plugin);
      $configuration->setPluginPath($plugin, dirname(dirname(__FILE__)).'/lib/plugins/'.$plugin);
    }
  }

  public function initialize()
  {
    $this->sympalConfiguration = new sfSympalConfiguration($this->dispatcher, $this->configuration);

    $this->dispatcher->connect('form.post_configure', array($this, 'formPostConfigure'));
  }

  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }

  public function formPostConfigure(sfEvent $event)
  {
    $form = $event->getSubject();
    if ($form instanceof sfFormDoctrine)
    {
      sfSympalFormToolkit::embedI18n($form->getObject(), $form);

      if (sfSympalConfig::get('remove_timestampable_from_forms', null, true))
      {
        unset($form['created_at'], $form['updated_at']);
      }
    }
  }
}