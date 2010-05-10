<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signinAsAdmin();

$browser->info('1 - Test the clear_cache action')
  ->get('/admin/clear_cache')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_admin')
    ->isParameter('action', 'clear_cache')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Clearing Cache/')
  ->end()
;

$caches = array(
  'config',
  'i18n',
  'routing',
  'module',
  'template',
  'menu',
);

$i = 1;
foreach ($caches as $cache)
{
  $browser->info(sprintf('  1.%d - Clearing %s cache', $i++, $cache))
    ->get('/admin/clear_cache?type='.$cache)
    
    ->with('response')->begin()
      ->isStatusCode(200)
    ->end()
  ;
}