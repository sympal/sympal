<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(53, new lime_output_color());

$configuration->loadHelpers(array('Tag'));

class sfSympalMenuTest extends sfSympalMenu
{
  
}

$menu = new sfSympalMenuTest('Test Menu');
$root1 = $menu->getChild('Root 1');
$root1->addChild('Child 1');
$last = $root1->addChild('Child 2');

$root2 = $menu->getChild('Root 2');
$child1 = $root2->addChild('Child 1');
$child2 = $child1->addChild('Child 2');

$t->is($root1->getLevel(), 0, 'Test root level is 0');
$t->is($root2->getLevel(), 0, 'Test root level is 0');
$t->is($child1->getLevel(), 1, 'Test level is 1');
$t->is($child2->getLevel(), 2, 'Test level is 2');
$t->is($child2->getPathAsString(), 'Test Menu > Root 2 > Child 1 > Child 2', 'Test getPathAsString()');
$t->is(get_class($root1), 'sfSympalMenuTest', 'Test children are created as same class as parent');

// array access
$t->is($menu['Root 1']['Child 1']->getName(), 'Child 1', 'Test getName()');

// countable
$t->is(count($menu), $menu->count(), 'Test sfSympalMenu Countable interface');
$t->is(count($root1), 2, 'Test sfSympalMenu Countable interface');

$count = 0;
foreach ($root1 as $key => $value)
{
  $count++;
  $t->is($key, 'Child '.$count, 'Test iteratable');
  $t->is($value->getLabel(), 'Child '.$count, 'Test iteratable');
}

$new = $menu['Root 2'];
$t->is(get_class($new), 'sfSympalMenuTest', 'Test child is correct class type');
$new2 = $new['Root 3']['Child 1'];
$t->is((string) $new, '<ul id="root-2-menu"><li id="test-menu-child-1" class="first">Child 1<ul id="child-1-menu"><li id="test-menu-child-2" class="first last">Child 2</li></ul></li><li id="test-menu-root-3" class="last">Root 3<ul id="root-3-menu"><li id="test-menu-child-1" class="first last">Child 1</li></ul></li></ul>', 'Test __toString()');

$menu['Test']['With Route']->setRoute('http://www.google.com');
$t->is((string) $menu['Test'], '<ul id="test-menu"><li id="test-menu-with-route" class="first last"><a href="http://www.google.com">With Route</a></li></ul>', 'Test __toString()');
$menu['Test']['With Route']->setOption('target', '_BLANK');
$t->is((string) $menu['Test'], '<ul id="test-menu"><li id="test-menu-with-route" class="first last"><a target="_BLANK" href="http://www.google.com">With Route</a></li></ul>', 'Test __toString()');

$menu['Test']['With Route']->requiresAuth(true);
$t->is((string) $menu['Test'], '', 'Test requiresAuth()');
$user = sfContext::getInstance()->getUser();
$user->setAuthenticated(true);
$t->is($user->isAuthenticated(), true, 'Test isAuthenticated()');
$t->is($menu['Test']['With Route']->checkUserAccess($user), true, 'Test checkUserAccess()');
$t->is((string) $menu['Test'], '<ul id="test-menu"><li id="test-menu-with-route" class="first last"><a target="_BLANK" href="http://www.google.com">With Route</a></li></ul>', 'Test authentication');
$menu->requiresNoAuth(true);
$t->is((string) $menu, '', 'Test requiresNoAuth)');
$t->is($menu->getLevel(), -1, 'Test getLevel()');
$t->is($menu['Test']['With Route']->getParent()->getLabel(), $menu['Test']->getLabel(), 'Test getLabel()');

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
), 'Test toArray()');

$test = new sfSympalMenuTest('Test');
$test->fromArray($menu['Root 4']->toArray());
$t->is($test->toArray(), $menu['Root 4']->toArray(), 'Test fromArray()');
$t->is($menu['Root 4']['Test']->getPathAsString(), 'Test Menu > Root 4 > Test', 'Test getPathAsString()');
$t->is($menu->getFirstChild()->getName(), 'Root 1', 'Test getFirstChild()');
$t->is($menu->getLastChild()->getName(), 'Root 4', 'Test getLastChild()');

class sfSympalMenuBreadcrumbsTest extends sfSympalMenuBreadcrumbs
{
  
}

$breadcrumbs = new sfSympalMenuBreadcrumbsTest('Doctrine');
$breadcrumbs->addChild('Documentation', 'http://www.doctrine-project.org/documentation');
$breadcrumbs->addChild('1.0', 'http://www.doctrine-project.org/documentation/1_0');
$node = $breadcrumbs->addChild('The Guide to Doctrine ORM', 'http://www.doctrine-project.org/documentation/1_0/manual');

$t->is(get_class($node), 'sfSympalMenuBreadcrumbsTest', 'Test Breadcrumbs class');
$t->is($breadcrumbs->getPathAsString(), 'Documentation / 1.0 / The Guide to Doctrine ORM', 'getPathAsString()');
$t->is((string) $breadcrumbs, '<div id="sympal_breadcrumbs"><ul id="doctrine-menu"><li id="doctrine-documentation" class="first"><a href="http://www.doctrine-project.org/documentation">Documentation</a></li><li id="doctrine-1-0"><a href="http://www.doctrine-project.org/documentation/1_0">1.0</a></li><li id="doctrine-the-guide-to-doctrine-orm" class="last"><a href="http://www.doctrine-project.org/documentation/1_0/manual">The Guide to Doctrine ORM</a></li></ul></div>', 'Test get breadcrumbs');

class sfSympalMenuSiteTest extends sfSympalMenuSite
{
  public function renderLink()
  {
    return $this->renderLabel();
  }
}

$manager = sfSympalMenuSiteManager::getInstance();
$primaryMenu = $manager->getMenu('primary', false, 'sfSympalMenuSiteTest');
$t->is((string) $primaryMenu, '<ul id="primary-menu"><li id="primary-home" class="first">Home</li><li id="primary-signout">Signout</li><li id="primary-sample-page">Sample Page</li><li id="primary-sample-content-list" class="last">Sample Content List</li></ul>', 'Test __toString()');

$split = $manager->split($primaryMenu, 2, true);
$total = $primaryMenu->count();
$t->is($split['primary']->count(), 2, 'Test count()');
$t->is((string) $split['primary'], '<ul id="primary-menu"><li id="primary-home" class="first">Home</li><li id="primary-signout">Signout</li></ul>', 'Test split() primary');
$t->is((string) $split['secondary'], '<ul id="secondary-menu"><li id="primary-sample-page">Sample Page</li><li id="primary-sample-content-list" class="last">Sample Content List</li></ul>', 'Test split() secondary');
$t->is($split['secondary']->count(), 2, 'Test secondary count()');

$footerMenu = $manager->getMenu('footer', false, 'sfSympalMenuSiteTest');
$t->is((string) $footerMenu, '<ul id="footer-menu"><li id="footer-sample-page" class="first">Sample Page</li><li id="footer-sample-content-list" class="last">Sample Content List</li></ul>', 'Test footer menu');

$menu = new sfSympalMenuTest('Test Menu');
$root1 = $menu->getChild('Root 1');
$first = $root1->addChild('Child 1');
$middle = $root1->addChild('Child 2');
$last = $root1->addChild('Child 3');

$t->is($first->isFirst(), true, 'Test isFirst()');
$t->is($last->isLast(), true, 'Test isLast()');
$t->is($middle->isFirst(), false, 'Test isFirst()');
$t->is($middle->isLast(), false, 'Test isLast()');
$t->is($first->getNum(), 1, 'Test getNum()');
$t->is($middle->getNum(), 2, 'Test getNum()');
$t->is($last->getNum(), 3, 'Test getNum()');

$table = Doctrine_Core::getTable('sfSympalMenuItem');

$menuItems = $table
  ->createQuery('m')
  ->execute();

$menuItem = $table
  ->createQuery('m')
  ->where('m.slug = ?', 'sample-page')
  ->fetchOne();

$t->is($menuItem->getIndentedName(), '- Sample Page', 'Test sfSympalMenuItem::getIndentedName()');
$t->is((string) $menuItem, '- Sample Page', 'Test sfSympalMenuItem::__toString()');
$t->is($menuItem->getMainContent()->getHeaderTitle(), 'Sample Page', 'Test sfSympalMenuItem::getHeaderTitle()');
$t->is($menuItem->getLabel(), 'Sample Page', 'Test sfSympalMenuItem::getLabel()');
$t->is($menuItem->getItemRoute(), '@page?slug=sample-page', 'Test sfSympalMenuItem::getItemRoute()');
$t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home / Sample Page', 'Test sfSympalBreadcrumbs::getPathAsString()');
$t->is($menuItem->getLayout(), 'sympal', 'Test getLayout()');

$menuManager = sfSympalMenuSiteManager::getInstance();
$menuManager->clear();

$profiler = new Doctrine_Connection_Profiler();
$conn = Doctrine_Manager::connection();
$conn->addListener($profiler);

$menuManager->initialize();

$count = 0;
foreach ($profiler as $event)
{
  if ($event->getName() == 'execute')
  {
    $count++;
  }
}
$t->is($count, 1, 'Test menus do not require more than one query');