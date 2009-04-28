<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

if (!isset($app))
{
  $app = 'sympal';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once(dirname(__FILE__).'/../bootstrap/cleanup.php');


require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);

sfSympalConfig::writeSetting(null, 'installed', false);
sfSympalConfig::set('installed', false);

sfContext::createInstance($configuration);

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/install');

$install = array(
  'install' => array(
    'first_name' => 'Jonathan',
    'last_name' => 'Wage',
    'email_address' => 'jonwage@gmail.com',
    'username' => 'jwage',
    'password' => 'changeme',
    'password_again' => 'changeme'
  )
);

$browser->
  click('Install Now', $install)->
  isRedirected()->
  followRedirect()->
  with('request')->begin()->
    isParameter('module', 'sympal_dashboard')->
  end()->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

sfSympalConfig::writeSetting(null, 'installed', false);