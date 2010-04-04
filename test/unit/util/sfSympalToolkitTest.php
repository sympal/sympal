<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(4);

$sympalContext = sfSympalContext::getInstance();
$t->is($sympalContext->getService('site_manager')->getSite()->getSlug(), $app, '->The current site matches the current app');

$t->info('Make a call to the test/test partial. It prints out the value of $var');
$resource = sfSympalToolkit::getSymfonyResource('test', 'test', array('var' => 'Unit Testing'));
$t->is($resource, 'Unit Testing', '::getSymfonyResource() called the correct partial, returned the correct value');

$t->is(sfSympalToolkit::getDefaultApplication(), 'sympal', '::getDefaultApplication() return "sympal"');
$t->is(in_array('en', sfSympalToolkit::getAllLanguageCodes()), true, '::getAllLanguageCodes() contains "en" - a sanity check.');