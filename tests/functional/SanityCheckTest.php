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
  post('/security/signin', array('signin' => array('username' => 'admin', 'password' => 'admin')))->
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
  post('/register/save', array('user' => array('first_name' => 'Jonathan', 'last_name' => 'Wage', 'email_address' => 'jonathan.wage@sensio.com', 'username' => 'test', 'password' => 'test', 'password_again' => 'test')))->
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
  post('/security/signin', array('signin' => array('username' => 'test', 'password' => 'test')))->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

$profiler = new Doctrine_Connection_Profiler();
$conn = Doctrine_Manager::connection();
$conn->addListener($profiler);

// Test base query count for pulling a page is 2
$browser->get('/pages/about');

$count = 0;
foreach ($profiler as $event)
{
  if ($event->getName() == 'execute')
  {
    $count++;
  }
}
$browser->test()->is($count, 4, 'Make sure we do not have more than 4 queries');