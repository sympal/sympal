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

  public static function enableSympalPlugins(ProjectConfiguration $configuration, $plugins = array())
  {
    if ($application = sfConfig::get('sf_app'))
    {
      $reflection = new ReflectionClass($application.'Configuration');
      if ($reflection->getConstant('disableSympal'))
      {
        return false;
      }
    }

    $configuration->enablePlugins('sfDoctrinePlugin');
    $configuration->enablePlugins('sfSympalPlugin');
    $configuration->enablePlugins(self::$dependencies);
    $configuration->enablePlugins($plugins);

    $sympalPluginPath = dirname(dirname(__FILE__));
    $configuration->setPluginPath('sfSympalPlugin', $sympalPluginPath);
    
    $embeddedPluginPath = $sympalPluginPath.'/lib/plugins';
    foreach (self::$dependencies as $plugin)
    {
      $configuration->setPluginPath($plugin, $embeddedPluginPath.'/'.$plugin);
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