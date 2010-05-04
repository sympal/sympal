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


$browser->info('4 - Set the layout in an action')
  ->get('/test/change_layout')
;

$response = $browser->getResponse();
$stylesheets = $response->getStylesheets();
$browser->test()->is(isset($stylesheets['test']), true, 'The response contains the "test" stylesheet');

$browser
  ->with('response')->begin()
    ->info('  4.1 - The correct layout is rendered')
    ->checkElement('h1', '/Test Layout/')
  ->end()
;