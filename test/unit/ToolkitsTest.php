<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(5);

$sympalContext = sfSympalContext::getInstance();

$menuItem = Doctrine_Core::getTable('sfSympalMenuItem')->findOneBySlug('home');
$sympalContext->setCurrentMenuItem($menuItem);

$t->is($sympalContext->getCurrentMenuItem(), $menuItem);
$t->is($sympalContext->getSite()->getSlug(), $app);

$resource = sfSympalToolkit::getSymfonyResource('test', 'test', array('var' => 'Test'));
$t->is($resource, 'Test');

$t->is(sfSympalToolkit::getDefaultApplication(), 'sympal');
$t->is(in_array('en', sfSympalToolkit::getAllLanguageCodes()), true);