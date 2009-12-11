<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$menuItem = Doctrine_Core::getTable('MenuItem')->findOneBySlug('sample-page');
sfSympalToolkit::setCurrentMenuItem($menuItem);

$t->is(sfSympalToolkit::getCurrentMenuItem(), $menuItem);
$t->is(sfSympalToolkit::getCurrentSite()->getSlug(), $app);

$resource = sfSympalToolkit::getSymfonyResource('test', 'test', array('var' => 'Test'));
$t->is($resource, 'Test');

$t->is(sfSympalToolkit::getDefaultApplication(), 'sympal');
$t->is(in_array('en', sfSympalToolkit::getAllLanguageCodes()), true);