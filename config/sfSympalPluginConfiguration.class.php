<?php

/**
 * Main Plugin configuration class for sympal.
 * 
 * This is responsible for loading in plugins that are core to sympal
 * 
 * @package     sfSympalPlugin
 * @subpackage  config
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalPluginConfiguration extends sfPluginConfiguration
{
  /**
   * sfSympalPlugin version number
   */
  const VERSION = '1.0.0-ALPHA3';

  /**
   * Array of all the core Sympal plugins
   * 
   * A core plugin is one that lives in the lib/plugins directory of sfSympalPlugin.
   * A core plugin will be enabled automatically
   */
  public static
    $corePlugins = array(
      'sfDoctrineGuardPlugin',
      'sfFormExtraPlugin',
      'sfTaskExtraPlugin',
      'sfFeed2Plugin',
      'sfWebBrowserPlugin',
      'sfJqueryReloadedPlugin',
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
      'sfSympalSearchPlugin'
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
    $this->sympalConfiguration = new sfSympalConfiguration($this->configuration);
  }

  /**
   * Shortcut method to enable all Sympal plugins for the given ProjectConfiguration
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