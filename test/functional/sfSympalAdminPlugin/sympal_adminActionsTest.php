<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$browser->info('2 - Test the signin action')
  ->get('/admin')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_admin')
    ->isParameter('action', 'signin')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Signin/')
  ->end()

    ->info('  2.1 - Sign into the form successfully')
  ->click('Signin', array('signin' => array(
    'username'  => 'admin',
    'password'  => 'admin',
  )))
  
  ->with('form')->begin()
    ->hasErrors(0)
  ->end()
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_admin')
    ->isParameter('action', 'signin')
  ->end()
  
  ->info('  2.2 - Post-signin should redirect to the dashboard')
  
  ->with('response')->begin()
    ->isRedirected()
  ->end()
  
  ->followRedirect()
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_dashboard')
    ->isParameter('action', 'index')
  ->end()

  ->info('  2.3 - Going back to /admin will automatically redirect to the dashboard')
  ->get('/admin')
  
  ->with('response')->begin()
    ->isRedirected()
  ->end()
  
  ->followRedirect()
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_dashboard')
    ->isParameter('action', 'index')
  ->end()
;

$browser->info('2 - Test the phpinfo page')
  ->get('/admin/phpinfo')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_admin')
    ->isParameter('action', 'phpinfo')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->matches('/PHP Version/')
  ->end()
;

$browser->signinAsAdmin();
$browser->info('3 - Test the clear_cache action')
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
  $browser->info(sprintf('  3.%d - Clearing %s cache', $i++, $cache))
    ->get('/admin/clear_cache?type='.$cache)
    
    ->with('response')->begin()
      ->isStatusCode(200)
    ->end()
  ;
}