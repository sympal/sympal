<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once(dirname(__FILE__).'/cleanup.php');

$projectPath = dirname(__FILE__).'/../fixtures/project';
require_once($projectPath.'/config/ProjectConfiguration.class.php');

if (!isset($app))
{
  $configuration = new ProjectConfiguration($projectPath);
} else {
  $configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
}

if (isset($database) && $database)
{
  $configuration->initializeSympal();

  $database = new sfDatabaseManager($configuration);

  if (isset($fixtures))
  {
    $configuration->loadFixtures($fixtures);
  }
}

if (isset($app))
{
  sfContext::createInstance($configuration);
}

require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

function sympal_autoload_again($class)
{
  $autoload = sfSimpleAutoload::getInstance();
  $autoload->reload();
  return $autoload->autoload($class);
}
spl_autoload_register('sympal_autoload_again');

require_once dirname(__FILE__).'/../../config/sfSympalPluginConfiguration.class.php';
$plugin_configuration = new sfSympalPluginConfiguration($configuration, dirname(__FILE__).'/../..');