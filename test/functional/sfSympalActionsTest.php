<?php

/**
 * A functional test for sfSympalActions - as close to a unit test as
 * we could get
 * 
 * @package     sfSympalPlugin 
 * @subpackage  test
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signInAsAdmin();

$browser->info('1 - Test the ask confirmation action')
  ->get('/test/ask_confirmation')
  ->click('Yes')

  ->with('request')->begin()
    ->isParameter('sympal_ask_confirmation', 1)
    ->isParameter('yes', 'Yes')
  ->end()

  ->with('response')->begin()
    ->matches('/Ok!/')
  ->end()

  ->get('/admin/dashboard')
  ->get('/test/ask_confirmation')
  ->click('No')

  ->with('response')->begin()
    ->isRedirected()
    ->followRedirect()
  ->end()

  ->with('request')->begin()
    ->isParameter('module', 'sympal_dashboard')
    ->isParameter('action', 'index')
  ->end()
;

$browser->info('2 - Test the ->forwardToRoute() method')

  ->info('  2.1 - Test using the ?var=val query params')
  ->get('/test/forward_to_route')

  ->with('request')->begin()
    ->isParameter('module', 'test')
    ->isParameter('action', 'route_to_forward_to')
    ->isParameter('param1', 'value1')
    ->isParameter('param2', 'value2')
  ->end()

  ->info('  2.2 - Test using the array formation of variables')
  ->get('/test/forward_to_route2')

  ->with('request')->begin()
    ->isParameter('module', 'test')
    ->isParameter('action', 'route_to_forward_to')
    ->isParameter('param1', 'value1')
    ->isParameter('param2', 'value2')
  ->end()
;

$browser->info('3 - Test the ->goBack() method')
  ->get('/test/start_go_back')
  ->get('/test/go_back')

  ->with('response')->begin()
    ->info('  3.1 - Using ->goBack() causes a redirect')
    ->isRedirected()
    ->followRedirect()
  ->end()

  ->with('request')->begin()
    ->info('  3.2 - The final resting place is the original action')
    ->isParameter('module', 'test')
    ->isParameter('action', 'start_go_back')
  ->end()
;

$site = Doctrine_Core::getTable('sfSympalSite')->createQuery('s')->fetchOne();
$site->theme = 'wordpress_default';
$site->save();

$browser->info('4 - Test theme-related operations')
  ->info('  4.1 - Goto a theme that sets nothing, see default theme')
  
  ->get('/theme/default')
  ->with('theme')->begin()
    ->isCurrentTheme('default')
  ->end()

  ->info('  4.2 - Goto an action and explicitly set the test theme')

  ->get('/theme/set_test_theme')
  ->with('theme')->begin()
    ->isCurrentTheme('test')
  ->end()
  
  ->info('  4.3 - Goto an action that uses the site\'s theme, which is set to wordpress')

  ->get('/theme/set_site_theme')
  ->with('theme')->begin()
    ->isCurrentTheme('wordpress_default')
  ->end()
;



