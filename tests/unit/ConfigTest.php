<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

sfSympalConfig::set('test', true);

$t->is(sfSympalConfig::get('test'), true);

sfSympalConfig::set('group', 'test', true);

$t->is(sfSympalConfig::get('group', 'test'), true);

$t->is(sfSympalConfig::get('doesnt_exists', null, 'default_value'), 'default_value');

sfSympalConfig::writeSetting('test_write_value', 1);

$path = sfConfig::get('sf_config_dir').'/app.yml';
$array = (array) sfYaml::load(file_get_contents($path));
$t->is(isset($array['all']['sympal_config']['test_write_value']), true);

$t->is(sfSympalConfig::isI18nEnabled(), true);
$t->is(sfSympalConfig::isI18nEnabled('Content'), true);