<?php
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/');

$menuItems = Doctrine_Core::getTable('MenuItem')->findAll();
foreach ($menuItems as $menuItem)
{
  if ($menuItem->level <= 0 || $menuItem->requires_auth || $menuItem->requires_no_auth || !($content = $menuItem->getMainContent()))
  {
    continue;
  }

  $browser->
    click($menuItem->getLabel())->
    with('response')->begin()->
      isStatusCode('200')->
    end()->
    with('request')->begin()->
      isParameter('module', 'sympal_content_renderer')->
      isParameter('action', 'index')->
    end()
  ;
}

$browser->
  get('/security/signin')->
  click('input[type="submit"]', array('signin' => array('username' => 'admin', 'password' => 'admin')), array('method' => 'post', '_with_csrf' => true))->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

$browser->
  get('/security/signout')->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()
;

$browser->
  get('/register')->
  click('input[type="submit"]', array('sf_guard_user' => array('first_name' => 'Jonathan', 'last_name' => 'Wage', 'email_address' => 'jonathan.wage@sensio.com', 'username' => 'test', 'password' => 'test', 'password_again' => 'test')), array('method' => 'post', '_with_csrf' => true))->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()
;

$browser->
  click('Signout')->
  with('response')->begin()->
    isRedirected()->
    followRedirect()->
  end()->
  click('Signin')->
  click('input[type="submit"]', array('signin' => array('username' => 'test', 'password' => 'test')), array('method' => 'post', '_with_csrf' => true))->
  with('user')->begin()->
    isAuthenticated()->
  end()->
  get('/security/signout')
;

$profiler = new Doctrine_Connection_Profiler();
$conn = Doctrine_Manager::connection();
$conn->addListener($profiler);

// Test base query count for pulling a page is 2
$browser->get('/pages/sample-page');

$count = 0;
foreach ($profiler as $event)
{
  if ($event->getName() == 'execute')
  {
    $count++;
  }
}
$browser->test()->is($count, 1, 'Make sure we do not have more than 1 query');