<?php

class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  /**
   * sfSympalPlugin version number
   */
  const VERSION = '1.0.0-ALPHA3';

  /**
   * Array of plugins sfSympalPlugin depends on. Used to autoenabled
   * all the plugins that make up sfSympalPlugin functionality as a whole.
   */
  public static
    $dependencies = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
      'sfThumbnailPlugin',
      'sfImageTransformPlugin',
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
      'sfSympalEditorPlugin',
      'sfSympalAssetsPlugin',
      'sfSympalContentSyntaxPlugin',
    );

  /**
   * Public reference to instanceof sfSympalConfiguration
   */
  public
    $sympalConfiguration;

  /**
   * sfSympalPluginConfiguration initialize() method instantiates the sfSympalConfiguration instance
   * for the current symfony dispatcher and configuration
   */
  public function initialize()
  {
    $this->sympalConfiguration = new sfSympalConfiguration($this->dispatcher, $this->configuration);
  }

  /**
   * Shortcut method to enable all Sympal plugins for the given ProjectConfniguration
   *
   * Returns an instance of sfSympalPluginEnabler which allows you to enable
   * disable and override any plugins with a convenient API.
   *
   * @param ProjectConfiguration $configuration
   * @return sfSympalPluginEnabler $enabler  Instance of sfSympalPluginEnabler
   */
  public static function enableSympalPlugins(ProjectConfiguration $configuration)
  {
    require_once(dirname(__FILE__).'/../lib/sfSympalPluginEnabler.class.php');

    $enabler = new sfSympalPluginEnabler($configuration);
    $enabler->enableSympalPlugins();

    return $enabler;
  }

  /**
   * Shortcut convenience method to get the current instance of sfSympalConfiguration
   *
   * @return sfSympalConfiguration $configuration
   */
  public function getSympalConfiguration()
  {
    return $this->sympalConfiguration;
  }
}