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
    'content_list' => array('rows_per_page' => 65),
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
$browser->test()->is($appYaml['all']['sympal_config']['content_list']['rows_per_page'], 65);