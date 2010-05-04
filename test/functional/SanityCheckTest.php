<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signInAsAdmin();
$browser->get('/');

$browser->info('1 - Test that all the menu items display with a link');
$menuItems = Doctrine_Core::getTable('sfSympalMenuItem')->findAll();
$i = 1;
foreach ($menuItems as $menuItem)
{
  if ($menuItem->level <= 0 || $menuItem->requires_auth || $menuItem->requires_no_auth || !($content = $menuItem->getContent()))
  {
    continue;
  }

  $browser
    ->info(sprintf('  1.%s - Checking %s menu item', $i++, $menuItem->getLabel()))

    ->click($menuItem->getLabel())
    ->with('response')->begin()
      ->isStatusCode('200')
    ->end()
    ->with('request')->begin()
      ->isParameter('module', 'sympal_content_renderer')
      ->isParameter('action', 'index')
    ->end()
  ;
}

$browser->info('2 - Testing the login to the admin')
  ->get('/security/signin')
  ->click('input[type="submit"]', array('signin' => array('username' => 'admin', 'password' => 'admin')), array('method' => 'post', '_with_csrf' => true))
  ->with('response')->begin()
    ->isRedirected()
    ->followRedirect()
  ->end()
  ->with('user')->begin()
    ->isAuthenticated()
  ->end()
  ->get('/admin/dashboard')
  ->with('request')->begin()
    ->isParameter('module', 'sympal_dashboard')
    ->isParameter('action', 'index')
  ->end()
;
$browser->signOut();


$browser->info('3 - Testing the registration form')
  ->get('/register')
  ->click('input[type="submit"]', array('sf_guard_user' => array('first_name' => 'Jonathan', 'last_name' => 'Wage', 'email_address' => 'jonathan.wage@sensio.com', 'username' => 'test', 'password' => 'test', 'password_again' => 'test')), array('method' => 'post', '_with_csrf' => true))
  ->with('response')->begin()
    ->isRedirected()
    ->followRedirect()
  ->end()

  ->info('  3.1 - Attempt to signin after the registration')
  ->signOut()
  ->click('Signin')
  ->click('input[type="submit"]', array('signin' => array('username' => 'test', 'password' => 'test')), array('method' => 'post', '_with_csrf' => true))
  ->with('user')->begin()
    ->isAuthenticated()
  ->end()
  ->signOut()
;


$browser->info('4 - Test that loading a page takes only one query');
$profiler = new Doctrine_Connection_Profiler();
$conn = Doctrine_Manager::connection();
$conn->addListener($profiler);

// Test base query count for pulling a page is 2
$browser->get('/pages/home');

$count = 0;
foreach ($profiler as $event)
{
  if ($event->getName() == 'execute')
  {
    $count++;
  }
}
$browser->test()->is($count, 1, 'Make sure we do not have more than 1 query');