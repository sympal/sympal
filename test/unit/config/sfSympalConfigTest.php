<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6);

sfSympalConfig::set('test', true);
$t->is(sfSympalConfig::get('test'), true, '->get() works with just one argument');

sfSympalConfig::set('group', 'test', true);
$t->is(sfSympalConfig::get('group', 'test'), true, '->get() works using the group arugment');

$t->is(sfSympalConfig::get('doesnt_exists', null, 'default_value'), 'default_value', '->get() returns a default value if the key does not exist');


sfSympalConfig::writeSetting('test_write_value', 1);
$path = sfConfig::get('sf_config_dir').'/app.yml';
$array = (array) sfYaml::load(file_get_contents($path));
$t->is(isset($array['all']['sympal_config']['test_write_value']), true, '->writeSetting() writes out correctly to the config/app.yml file');

$t->is(sfSympalConfig::isI18nEnabled(), false, '->isI18nEnabled() correctly returns false');
$t->is(sfSympalConfig::isI18nEnabled('sfSympalContent'), false, '->isI18nEnabled($modelName) correctly returns false');