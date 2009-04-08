<?php
$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

$menuItem = Doctrine::getTable('MenuItem')->findOneBySlug('about');
$t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home > About');