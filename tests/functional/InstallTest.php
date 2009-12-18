<?php

if (!isset($app))
{
  $app = 'sympal';
}

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);

require_once(dirname(__FILE__).'/../bootstrap/cleanup.php');

sfSympalConfig::writeSetting('installed', false);

sfContext::createInstance($configuration);

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/install');

$install = array(
  'install' => array(
    'user' => array(
      'first_name' => 'Jonathan',
      'last_name' => 'Wage',
      'email_address' => 'jonwage@gmail.com',
      'username' => 'jwage',
      'password' => 'changeme',
      'password_again' => 'changeme'
    )
  )
);

$browser->
  click('Install Now', $install)->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('request')->begin()->
    isParameter('module', 'sympal_dashboard')->
  end()->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

sfSympalConfig::writeSetting('installed', true);