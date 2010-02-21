<?php

if (!isset($app))
{
  $app = '##APP_NAME##';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

function ##PLUGIN_NAME##_cleanup()
{
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/cache');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../fixtures/project/log');
}
##PLUGIN_NAME##_cleanup();
register_shutdown_function('##PLUGIN_NAME##_cleanup');

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);
