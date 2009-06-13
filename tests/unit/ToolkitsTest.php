<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$menuItem = Doctrine::getTable('MenuItem')->findOneBySlug('about');
sfSympalToolkit::setCurrentMenuItem($menuItem);

$t->is(sfSympalToolkit::getCurrentMenuItem(), $menuItem);
$t->is(sfSympalToolkit::getCurrentSite()->getSlug(), $app);

$resource = sfSympalToolkit::getSymfonyResource('test', 'email', array(
  'variable' => 'Variable 1',
  'variable2' => 'Variable 2'
));
$t->is($resource, 'Subject
Body Variable 1 Variable 2');

$t->is(sfSympalToolkit::getDefaultApplication(), 'sympal');
$t->is(in_array('en', sfSympalToolkit::getAllLanguageCodes()), true);