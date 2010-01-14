<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signInAsAdmin();

$browser->
  get('/admin/configuration')->
  click('Save', array('settings' => array('breadcrumbs_separator' => ' :: ', 'plugin_api' => array('username' => 'test', 'password' => 'test'))))
;

$after = sfYaml::load(sfConfig::get('sf_config_dir').'/app.yml');
$t = $browser->test();
$t->is($after['all']['sympal_config']['breadcrumbs_separator'], ' :: ', 'Test breadcrumbs separator saved');
$t->is($after['all']['sympal_config']['plugin_api']['username'], 'test', 'Test plugin_api.username saved');
$t->is($after['all']['sympal_config']['plugin_api']['password'], 'test', 'Test plugin_api.password saved');

sfSympalConfig::writeSetting('breadcrumbs_separator', ' / ');