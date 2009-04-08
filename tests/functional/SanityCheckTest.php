<?php
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/');

$menuItems = Doctrine::getTable('MenuItem')->findAll();
foreach ($menuItems as $menuItem)
{
  if ($menuItem->level <= 0 || $menuItem->requires_auth || $menuItem->requires_no_auth || !($content = $menuItem->getMainContent()))
  {
    continue;
  }

  $browser->
    click($menuItem->getLabel())->
    isStatusCode('200')->
    with('request')->begin()->
      isParameter('module', 'sympal_content_renderer')->
      isParameter('action', 'index')->
    end()->
    with('response')->begin()->
      contains(($menuItem->getBreadcrumbs()->count() ? (string) $menuItem->getBreadcrumbs():''))->
      contains($content->getSlots()->getFirst()->render())->
      contains($content->getSlots()->getLast()->render())->
    end();
}

$browser->
  get('/security/signin')->
  post('/security/signin', array('signin' => array('username' => 'admin', 'password' => 'admin')))->
  isRedirected()->
  followRedirect()->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

$browser->
  get('/security/signout')->
  isRedirected()->
  followRedirect()
;

$browser->
  get('/register')->
  post('/register/save', array('user' => array('first_name' => 'Jonathan', 'last_name' => 'Wage', 'email_address' => 'jonathan.wage@sensio.com', 'username' => 'test', 'password' => 'test', 'password_again' => 'test')))->
  isRedirected()->
  followRedirect()
;

$browser->
  click('Signout')->
  isRedirected()->
  followRedirect()->
  click('Signin')->
  post('/security/signin', array('signin' => array('username' => 'test', 'password' => 'test')))->
  with('user')->begin()->
    isAuthenticated()->
  end()
;