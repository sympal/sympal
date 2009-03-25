<?php
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(8, new lime_output_color());

class sfSympalMenuTest extends sfSympalMenu
{
  
}

$menu = new sfSympalMenuTest('Test Menu');
$root1 = $menu->getChild('Root 1');
$root1->addChild('Child 1');
$root1->addChild('Child 2');

$root2 = $menu->getChild('Root 2');
$child1 = $root2->addChild('Child 1');
$child2 = $child1->addChild('Child 2');

$t->is($root1->getLevel(), 0);
$t->is($root2->getLevel(), 0);
$t->is($child1->getLevel(), 1);
$t->is($child2->getLevel(), 2);
$t->is($child2->getPathAsString(), 'Test Menu > Root 2 > Child 1 > Child 2');
$t->is(get_class($root1), 'sfSympalMenuTest');

class sfSympalMenuBreadcrumbsTest extends sfSympalMenuBreadcrumbs
{
  
}

$breadcrumbs = new sfSympalMenuBreadcrumbsTest('Doctrine');
$breadcrumbs->addChild('Documentation');
$breadcrumbs->addChild('1.0');
$node = $breadcrumbs->addChild('The Guide to Doctrine ORM');

$t->is(get_class($node), 'sfSympalMenuBreadcrumbsTest');
$t->is($breadcrumbs->getPathAsString(), 'Documentation > 1.0 > The Guide to Doctrine ORM');