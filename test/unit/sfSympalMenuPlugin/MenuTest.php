<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(55);

$configuration->loadHelpers(array('Tag'));

class sfSympalMenuTest extends sfSympalMenu
{
  
}

$t->info('Menu Structure');
$t->info('   rt1     rt2 ');
$t->info('  /  \      |  ');
$t->info('ch1   ch2  ch3 ');
$t->info('            |  ');
$t->info('           gc1 ');

$menu = new sfSympalMenuTest('Test Menu');
$root1 = $menu->getChild('Root 1');
$child1 = $root1->addChild('Child 1');
$child2 = $root1->addChild('Child 2');

$root2 = $menu->getChild('Root 2');
$child3 = $root2->addChild('Child 3');
$grandchild1 = $child3->addChild('Grandchild 1');

$t->info('1 - Test the basics of the hierarchy');

$t->is($menu->getLevel(), -1, 'Test getLevel()');
$t->is($root1->getLevel(), 0, 'Test Root 1 level is 0');
$t->is($root2->getLevel(), 0, 'Test Root 2 level is 0');
$t->is($child3->getLevel(), 1, 'Test Child 3 level is 1');
$t->is($grandchild1->getLevel(), 2, 'Test Grandchild 1 level is 2');
$t->is($grandchild1->getPathAsString(), 'Test Menu > Root 2 > Child 3 > Grandchild 1', 'Test getPathAsString() on Grandchild 1');
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

$t->is(get_class($menu['Root 2']), 'sfSympalMenuTest', 'Test child "Root 2" is correct class type');

$t->info('Add another child and grandchild to Root 2');
$t->info('   rt1        rt2    ');
$t->info('  /  \       /   \   ');
$t->info('ch1   ch2  ch3   ch4 ');
$t->info('            |     |  ');
$t->info('           gc1   gc2 ');

$menu['Root 2']['Child 4']['Grandchild 2'];
$t->is((string) $menu['Root 2'], '<ul id="root-2-menu"><li id="test-menu-child-3" class="first">Child 3<ul id="child-3-menu"><li id="test-menu-grandchild-1" class="first last">Grandchild 1</li></ul></li><li id="test-menu-child-4" class="last">Child 4<ul id="child-4-menu"><li id="test-menu-grandchild-2" class="first last">Grandchild 2</li></ul></li></ul>', 'Test __toString()');


$t->info('2 - Test routes, authentication');

$t->info('Add a third route to check routes, authentication');
$t->info('   rt1        rt2        rt3   ');
$t->info('  /  \       /   \        |    ');
$t->info('ch1   ch2  ch3   ch4   w/route ');
$t->info('            |     |            ');
$t->info('           gc1   gc2           ');

$menu['Root 3']['With Route']->setRoute('http://www.google.com');
$t->is((string) $menu['Root 3'], '<ul id="root-3-menu"><li id="test-menu-with-route" class="first last"><a href="http://www.google.com">With Route</a></li></ul>', 'Test __toString() with a route');

$menu['Root 3']['With Route']->setOption('target', '_BLANK');
$t->is((string) $menu['Root 3'], '<ul id="root-3-menu"><li id="test-menu-with-route" class="first last"><a target="_BLANK" href="http://www.google.com">With Route</a></li></ul>', 'Test __toString() with a target option');

$t->is($menu['Root 3']->hasChildren(), true, 'Test hasChildren() on Root 3');

$menu['Root 3']['With Route']->requiresAuth(true);
$t->is((string) $menu['Root 3'], '', 'Test requiresAuth()');
$t->is($menu['Root 3']->hasChildren(), false, 'Test hasChildren() on Root 3 when user has no access to With Route');

$user = sfContext::getInstance()->getUser();
$user->setAuthenticated(true);
$t->is($user->isAuthenticated(), true, 'Test isAuthenticated()');
$t->is($menu['Root 3']['With Route']->checkUserAccess($user), true, 'Test checkUserAccess()');
$t->is((string) $menu['Root 3'], '<ul id="root-3-menu"><li id="test-menu-with-route" class="first last"><a target="_BLANK" href="http://www.google.com">With Route</a></li></ul>', 'Test authentication');
$menu->requiresNoAuth(true);
$t->is((string) $menu, '', 'Test requiresNoAuth()');
$t->is($menu['Root 3']['With Route']->getParent()->getLabel(), $menu['Root 3']->getLabel(), 'Test getLabel()');

$t->info('3 - Test isCurrent(), toArray() and child calls');

$t->info('Add a 4th root with child and make it current (~ for current)');
$t->info('   rt1        rt2        rt3      rt4 ');
$t->info('  /  \       /   \        |        |  ');
$t->info('ch1   ch2  ch3   ch4   w/route  ~Test ');
$t->info('            |     |                   ');
$t->info('           gc1   gc2                  ');

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


$t->info('4 - Test some positional functions');
$t->info('     root1     ');
$t->info('    /  |  \    ');
$t->info('first mid last ');
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


$t->info('5 - Test the breadcrumbs menu item');

class sfSympalMenuBreadcrumbsTest extends sfSympalMenuBreadcrumbs
{
  
}

$t->info('Create a basic hierarchy');
$t->info('    Docs    ');
$t->info('     |      ');
$t->info('    1.0     ');
$t->info('     |      ');
$t->info('  Doctrine  ');

$breadcrumbs = new sfSympalMenuBreadcrumbsTest('Doctrine');
$breadcrumbs->addChild('Documentation', 'http://www.doctrine-project.org/documentation');
$breadcrumbs->addChild('1.0', 'http://www.doctrine-project.org/documentation/1_0');
$node = $breadcrumbs->addChild('The Guide to Doctrine ORM', 'http://www.doctrine-project.org/documentation/1_0/manual');

$t->is(get_class($node), 'sfSympalMenuBreadcrumbsTest', 'Test Breadcrumbs class');
$t->is($breadcrumbs->getPathAsString(), 'Documentation / 1.0 / The Guide to Doctrine ORM', 'getPathAsString()');
$t->is((string) $breadcrumbs, '<div id="sympal_breadcrumbs"><ul id="doctrine-menu"><li id="doctrine-documentation" class="first"><a href="http://www.doctrine-project.org/documentation">Documentation</a></li><li id="doctrine-1-0"><a href="http://www.doctrine-project.org/documentation/1_0">1.0</a></li><li id="doctrine-the-guide-to-doctrine-orm" class="last"><a href="http://www.doctrine-project.org/documentation/1_0/manual">The Guide to Doctrine ORM</a></li></ul></div>', 'Test get breadcrumbs');


$t->info('6 - Test the menu site class');

class sfSympalMenuSiteTest extends sfSympalMenuSite
{
  public function renderLink()
  {
    return $this->renderLabel();
  }
}

$user = Doctrine_Core::getTable('sfGuardUser')->findOneByIsSuperAdmin(true);
sfContext::getInstance()->getUser()->signIn($user);

$menuManager = sfSympalContext::getInstance()->getService('menu_manager');

$t->info('Retrieve the "primary" menu, setup in the fixtures');
$t->info('blog  signout  home  sample-page  sample-content-list    powered-by         ');
$t->info('                                                        /     |     \       ');
$t->info('                                                  symfony  doctrine  sympal ');

$primaryMenu = $menuManager->getMenu('primary', false, 'sfSympalMenuSiteTest');
$t->is((string) $primaryMenu, '<ul id="primary-menu"><li id="primary-blog" class="first">Blog</li><li id="primary-signout">Signout</li><li id="primary-home">Home</li><li id="primary-sample-page">Sample Page</li><li id="primary-sample-content-list">Sample Content List</li><li id="primary-powered-by" class="last">Powered By</li></ul>', 'Test __toString() without showing children');

$split = $menuManager->split($primaryMenu, 2, true);
$total = $primaryMenu->count();
$t->is($split['primary']->count(), 2, 'Test count() after splitting the menu into 2 pieces');
$t->is((string) $split['primary'], '<ul id="primary-menu"><li id="primary-blog" class="first">Blog</li><li id="primary-signout">Signout</li></ul>', 'Test split() primary');
$t->is((string) $split['secondary'], '<ul id="secondary-menu"><li id="primary-home">Home</li><li id="primary-sample-page">Sample Page</li><li id="primary-sample-content-list">Sample Content List</li><li id="primary-powered-by" class="last">Powered By</li></ul>', 'Test split() secondary');
$t->is($split['secondary']->count(), 4, 'Test secondary count()');

$footerMenu = $menuManager->getMenu('footer', false, 'sfSympalMenuSiteTest');
$t->is((string) $footerMenu, '', 'Test footer menu');


$table = Doctrine_Core::getTable('sfSympalMenuItem');

$menuItems = $table
  ->createQuery('m')
  ->execute();

$menuItem = $table
  ->createQuery('m')
  ->where('m.slug = ?', 'home')
  ->fetchOne();

$t->is($menuItem->getIndentedName(), '- Home', 'Test sfSympalMenuItem::getIndentedName()');
$t->is((string) $menuItem, '- Home', 'Test sfSympalMenuItem::__toString()');
$t->is($menuItem->getContent()->getHeaderTitle(), 'Home', 'Test sfSympalMenuItem::getHeaderTitle()');
$t->is($menuItem->getLabel(), 'Home', 'Test sfSympalMenuItem::getLabel()');
$t->is($menuItem->getItemRoute(), '@sympal_content_home', 'Test sfSympalMenuItem::getItemRoute()');
$t->is($menuItem->getBreadcrumbs()->getPathAsString(), '', 'Test sfSympalBreadcrumbs::getPathAsString() returns nothing for home');

$menuItem = $table
  ->createQuery('m')
  ->where('m.slug = ?', 'sample-page')
  ->fetchOne();

$t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home / Sample Page', 'Test sfSympalBreadcrumbs::getPathAsString() returns nothing for home');

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