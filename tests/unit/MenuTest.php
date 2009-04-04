<?php
$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(38, new lime_output_color());

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

// array access
$t->is($menu['Root 1']['Child 1']->getName(), 'Child 1');

// countable
$t->is(count($menu), $menu->count());
$t->is(count($root1), 2);

$count = 0;
foreach ($root1 as $key => $value)
{
  $count++;
  $t->is($key, 'Child '.$count);
  $t->is($value->getLabel(), 'Child '.$count);
}

$new = $menu['Root 2'];
$t->is(get_class($new), 'sfSympalMenuTest');
$new2 = $new['Root 3']['Child 1'];
$t->is((string) $new, '<ul id="root-2"><li id="child-1">Child 1<ul id="child-1"><li id="child-2">Child 2</li></ul></li><li id="root-3">Root 3<ul id="root-3"><li id="child-1">Child 1</li></ul></li></ul>');

$menu['Test']['With Route']->setRoute('http://www.google.com');
$t->is((string) $menu['Test'], '<ul id="test"><li id="with-route"><a href="http://www.google.com">With Route</a></li></ul>');
$menu['Test']['With Route']->setOption('target', '_BLANK');
$t->is((string) $menu['Test'], '<ul id="test"><li id="with-route"><a target="_BLANK" href="http://www.google.com">With Route</a></li></ul>');

$menu['Test']['With Route']->requiresAuth(true);
$t->is((string) $menu['Test'], '<ul id="test"></ul>');
$user = sfContext::getInstance()->getUser();
$user->setAuthenticated(true);
$t->is($user->isAuthenticated(), true);
$t->is($menu['Test']['With Route']->checkUserAccess($user), true);
$t->is((string) $menu['Test'], '<ul id="test"><li id="with-route"><a target="_BLANK" href="http://www.google.com">With Route</a></li></ul>');
$menu->requiresNoAuth(true);
$t->is((string) $menu, '');
$t->is($menu->getLevel(), -1);
$t->is($menu['Test']['With Route']->getParent()->getLabel(), $menu['Test']->getLabel());

$menu['Root 4']['Test']->isCurrent(true);
$t->is($menu['Root 4']->toArray(), array(
  'name' => 'Root 4',
  'level' => 0,
  'is_current' => false,
  'options' => array(),
  'children' => array(
    'Test' => array(
      'name' => 'Test',
      'level' => 1,
      'is_current' => true,
      'options' => array()
    )
  )
));

$test = new sfSympalMenuTest('Test');
$test->fromArray($menu['Root 4']->toArray());
$t->is($test->toArray(), $menu['Root 4']->toArray());
$t->is($menu['Root 4']['Test']->getPathAsString(), 'Test Menu > Root 4 > Test');
$t->is($menu->getFirstChild()->getName(), 'Root 1');
$t->is($menu->getLastChild()->getName(), 'Root 4');

class sfSympalMenuBreadcrumbsTest extends sfSympalMenuBreadcrumbs
{
  
}

$breadcrumbs = new sfSympalMenuBreadcrumbsTest('Doctrine');
$breadcrumbs->addChild('Documentation', 'http://www.doctrine-project.org/documentation');
$breadcrumbs->addChild('1.0', 'http://www.doctrine-project.org/documentation/1_0');
$node = $breadcrumbs->addChild('The Guide to Doctrine ORM', 'http://www.doctrine-project.org/documentation/1_0/manual');

$t->is(get_class($node), 'sfSympalMenuBreadcrumbsTest');
$t->is($breadcrumbs->getPathAsString(), 'Documentation > 1.0 > The Guide to Doctrine ORM');
$t->is((string) $breadcrumbs, '<div id="sympal_breadcrumbs"><ul id="doctrine"><li id="documentation"><a href="http://www.doctrine-project.org/documentation">Documentation</a></li><li id="1-0"><a href="http://www.doctrine-project.org/documentation/1_0">1.0</a></li><li id="the-guide-to-doctrine-orm"><a href="http://www.doctrine-project.org/documentation/1_0/manual">The Guide to Doctrine ORM</a></li></ul></div>');

$manager = sfSympalMenuSiteManager::getInstance();
$primaryMenu = $manager->getMenu('primary');
$t->is((string) $primaryMenu, '<ul id="primary"><li id="signout"><a href="MenuTest.php/MenuTest.php/security/signout">Signout</a></li><li id="pages"><a href="MenuTest.php/MenuTest.php/pages">Pages</a></li><li id="about"><a href="MenuTest.php/MenuTest.php/pages/about">About</a><ul id="about"><li id="doctrine"><a href="http://www.doctrine-project.org/about">Doctrine</a></li><li id="symfony"><a href="http://www.symfony-project.org/about">symfony</a></li><li id="sensio-labs"><a href="http://www.sensiolabs.com">Sensio Labs</a></li><li id="centre-source"><a href="http://www.centresource.com">centre{source}</a></li></ul></li><li id="markdown-examples"><a href="MenuTest.php/MenuTest.php/pages/markdown-examples">Markdown Examples</a></li><li id="readme"><a href="MenuTest.php/MenuTest.php/pages/readme">README</a></li><li id="trac"><a href="http://trac.jwage.com/sympal">Trac</a></li></ul>');

$split = $manager->split($primaryMenu, 2, true);
$total = $primaryMenu->count();
$t->is($split['primary']->count(), 2);
$t->is((string) $split['primary'], '<ul id="primary"><li id="signout"><a href="MenuTest.php/MenuTest.php/security/signout">Signout</a></li><li id="pages"><a href="MenuTest.php/MenuTest.php/pages">Pages</a></li></ul>');
$t->is((string) $split['secondary'], '<ul id="secondary"><li id="about"><a href="MenuTest.php/MenuTest.php/pages/about">About</a><ul id="about"><li id="doctrine"><a href="http://www.doctrine-project.org/about">Doctrine</a></li><li id="symfony"><a href="http://www.symfony-project.org/about">symfony</a></li><li id="sensio-labs"><a href="http://www.sensiolabs.com">Sensio Labs</a></li><li id="centre-source"><a href="http://www.centresource.com">centre{source}</a></li></ul></li><li id="markdown-examples"><a href="MenuTest.php/MenuTest.php/pages/markdown-examples">Markdown Examples</a></li><li id="readme"><a href="MenuTest.php/MenuTest.php/pages/readme">README</a></li><li id="trac"><a href="http://trac.jwage.com/sympal">Trac</a></li></ul>');
$t->is($split['secondary']->count(), 4);

$footerMenu = $manager->getMenu('footer');
$t->is((string) $footerMenu, '<ul id="footer"><li id="about"><a href="MenuTest.php/MenuTest.php/pages/about">About</a></li><li id="markdown-examples"><a href="MenuTest.php/MenuTest.php/pages/markdown-examples">Markdown Examples</a></li><li id="readme"><a href="MenuTest.php/MenuTest.php/pages/readme">README</a></li><li id="sitemap"><a href="MenuTest.php/MenuTest.php/admin/sitemap">Sitemap</a></li></ul>');