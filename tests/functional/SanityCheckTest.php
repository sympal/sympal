<?php
$app = 'frontend';
$database = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$configuration->loadHelpers(array('Url', 'Entity'));

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/');

$menuItems = Doctrine::getTable('MenuItem')->findAll();
foreach ($menuItems as $menuItem)
{
  if ($menuItem->level <= 0 || $menuItem->requires_auth || $menuItem->requires_no_auth || !($entity = $menuItem->getMainEntity()))
  {
    continue;
  }

  $browser->
    click($menuItem->getLabel())->
    isStatusCode('200')->
    with('request')->begin()->
      isParameter('module', 'sympal_entity')->
      isParameter('action', $menuItem->getLabel() == 'Pages' ? 'index':'view')->
    end()->
    with('response')->begin()->
      contains((string) $menuItem->getBreadcrumbs())->
      contains(render_entity_slot($entity->getSlots()->getFirst()))->
      contains(render_entity_slot($entity->getSlots()->getLast()))->
    end();
}

$browser->
  get('/login')->
  click('sign in', array('signin' => array('username' => 'admin', 'password' => 'admin')))->
  isRedirected()->
  followRedirect()->
  with('user')->begin()->
    isAuthenticated()->
  end()
;

$browser->
  get('/logout')->
  isRedirected()->
  followRedirect()
;

$browser->
  get('/register')->
  post('/register/save', array('sf_guard_user' => array('username' => 'test', 'password' => 'test', 'password_again' => 'test')))->
  isRedirected()->
  followRedirect()
;

$browser->
  click('Logout')->
  isRedirected()->
  followRedirect()->
  click('Login')->
  click('sign in', array('signin' => array('username' => 'test', 'password' => 'test')))->
  with('user')->begin()->
    isAuthenticated()->
  end()
;