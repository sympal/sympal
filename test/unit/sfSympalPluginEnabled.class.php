<?php

/**
 * Unit test for sympal's plugin enabler
 * 
 * @package     sfSympalPlugin
 * @subpackage  test
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */

require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(16);

// test stub class
class ProjectConfigurationStub extends sfProjectConfiguration
{
  public function loadPlugins()
  {
    parent::loadPlugins();
    
    $this->pluginsLoaded = false;
  }
}

// test stub classes for application configuration
class ApplicationStubConfiugration extends sfApplicationConfiguration
{
  // prevents the loading of plugins via ProjectConfiguration (to start fresh)
  public function setup()
  {
  }
  public function loadPlugins()
  {
    parent::loadPlugins();
    $this->pluginsLoaded = false;
  }
}
class enabledApplicationStubConfiguration extends ApplicationStubConfiugration
{
}
class disabledApplicationStubConfiguration extends ApplicationStubConfiugration
{
  const disableSympal = true;
}

// setup some configurations
$projConfiguration = new ProjectConfigurationStub();
$appEnabledConfiguration = new enabledApplicationStubConfiguration('test', true);
$appDisabledConfiguration = new disabledApplicationStubConfiguration('test', true);

// setup some enablers
$enabler = new sfSympalPluginEnabler($projConfiguration);
$enablerApp1 = new sfSympalPluginEnabler($appEnabledConfiguration);
$enablerApp2 = new sfSympalPluginEnabler($appDisabledConfiguration);

$t->info('1 - Test the isSympalEnabled() functionality');
$t->is($enabler->isSympalEnabled(), true, '->isSympalEnabled() returns true for all ProjectConfiguration instances');
$t->is($enablerApp1->isSympalEnabled(), true, '->isSympalEnabled() returns true for an ApplicationConfiguration instance without the disableSympal constant');
$t->is($enablerApp2->isSympalEnabled(), false, '->isSympalEnabled() returns false for an ApplicationConfiguration instance WITH the disableSympal constant equal to true');


$t->info('2 - Test the enabling/disabling of plugins');
$t->info('  2.1 - Sanity checks');
$t->is(count($projConfiguration->getPlugins()), 0, 'Initially there are 0 enabled plugins');
$t->is(count($appEnabledConfiguration->getPlugins()), 0, 'Initially there are 0 enabled plugins');
$t->is(count($appDisabledConfiguration->getPlugins()), 0, 'Initially there are 0 enabled plugins');

$t->info('  2.2 - Using enableSympalCorePlugins() for a plugin that does not exist inside sympal throws an exception');
try
{
  $enabler->enableSympalCorePlugins('fake');
  $t->fail('Exception not thrown');
}
catch (sfException $e)
{
  $t->pass('Exception thrown');
}

$t->info('  2.3 - Enable a core plugin');
$enabler->enableSympalCorePlugins('sfDoctrineGuardPlugin');
$t->is(count($projConfiguration->getPlugins()), 1, '->enableSympalCorePlugins() enables the core plugin');
$paths = $projConfiguration->getPluginPaths();
$t->is($paths[0], realpath(dirname(__FILE__).'/../../lib/plugins/sfDoctrineGuardPlugin'), 'The plugin path of the core plugin is inside the lib/plugins dir of sympal');

$t->info('  2.4 - Disable a core plugin');
$enabler->disableSympalCorePlugins(array('sfDoctrineGuardPlugin'));
$t->is(count($projConfiguration->getPlugins()), 0, '->disableSympalCorePlugins() disables the core plugin');

$corePluginsCount = count(sfSympalPluginConfiguration::$corePlugins);
$installedPluginsCount = 3; // in the test project's plugin directory
$otherPluginsCount = 2; // sfSympalPlugin + sfDoctrinePlugin
$totalPluginsCount = $corePluginsCount + $installedPluginsCount + $otherPluginsCount;

$enabler->enableSympalPlugins();
$enablerApp1->enableSympalPlugins();
$enablerApp2->enableSympalPlugins();

$t->is(count($projConfiguration->getPlugins()), $totalPluginsCount, '->enableSympalPlugins() enables core plugins + anything in the plugins directory');
$t->is(count($appEnabledConfiguration->getPlugins()), $totalPluginsCount, '->enableSympalPlugins() enables core plugins + anything in the plugins directory');
$t->is(count($appDisabledConfiguration->getPlugins()), 0, '->enableSympalPlugins() enables no plugins if sympal is disabled for the application');

$t->info('  2.5 - Test overrideSympalPlugin()');
$projConfiguration->disablePlugins($projConfiguration->getPlugins());
$enabler->enableSympalCorePlugins(array('sfDoctrineGuardPlugin'));
$t->is(count($projConfiguration->getPlugins()), 1, 'Sanity check');
$enabler->overrideSympalPlugin('sfDoctrineGuardPlugin');
$t->is(count($projConfiguration->getPlugins()), 1, 'After overrideSympalPlugin(), the sfDoctrineGuardPlugin is still enabled');
$paths = $projConfiguration->getPluginPaths();

/*
 * The plugin path is false because it doesn't exist in the plugins directory.
 * I can't think of a good way to test this short of creating an sfDoctrineGuardPlugin
 * somewhere and pointing there. sfProjectConfiguration calls realpath, so
 * my attempts to set it to a non-existent dir result in a path of "false"
 */
$t->is($paths[0], false, 'The plugin path to the plugin has changed, however');