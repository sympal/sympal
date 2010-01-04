<?php

if (!isset($app))
{
  $app = 'sympal';
}

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);

require_once(dirname(__FILE__).'/cleanup.php');
require_once(sfConfig::get('sf_lib_dir').'/sfSympalTestFunctional.class.php');

sfContext::createInstance($configuration);

$configuration->initializeSympal();