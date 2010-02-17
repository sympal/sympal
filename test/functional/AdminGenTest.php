<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signInAsAdmin();

$adminGenModules = array(
  'sympal_users' => '/admin/security/user',
  'sympal_groups' => '/admin/security/group',
  'sympal_permissions' => '/admin/security/permission',
  'sympal_content' => '/admin/content',
  'sympal_content_types' => '/admin/content/types',
  'sympal_sites' => '/admin/sites'
);

foreach ($adminGenModules as $adminGenModule => $url)
{
  $browser->
    get($url)->
    with('response')->begin()->
      isStatusCode('200')->
    end()->
    with('request')->begin()->
      isParameter('module', $adminGenModule)->
      isParameter('action', 'index')->
    end()->
    click('Edit')->
    with('request')->begin()->
      isParameter('module', $adminGenModule)->
      isParameter('action', 'edit')->
    end()->    
    click('Save', array(), array('_with_csrf' => true))->
    with('response')->begin()->
      isStatusCode('302')->
      isRedirected()->
      followRedirect()->
    end()
  ;
}