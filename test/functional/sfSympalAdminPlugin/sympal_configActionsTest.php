<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signinAsAdmin();

$browser->info('1 - Test some basic submission of the config form')
  ->get('/admin/configuration')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_config')
    ->isParameter('action', 'index')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/System Settings/')
    ->checkForm('sfSympalConfigForm')
  ->end()

  ->info('  1.1 - Change a few settings and save')
  ->click('Save', array('settings' => array(
    'default_rendering_action' => 'test',
    'form' => array('recaptcha_private_key' => '1234test'),
  )))
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_config')
    ->isParameter('action', 'save')
  ->end()
  
  ->with('response')->begin()
    ->isRedirected()
  ->end()
  
  ->followRedirect()
;

$browser->info('  1.2 - Check that the application app.yml was updated with the changed config');
$appYaml = sfYaml::load(sfConfig::get('sf_apps_dir').'/sympal/config/app.yml');
$browser->test()->is($appYaml['all']['sympal_config']['default_rendering_action'], 'test');
$browser->test()->is($appYaml['all']['sympal_config']['form']['recaptcha_private_key'], '1234test');