<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$browser->info('1 - Test the sitemap.xml')
  ->get('/sitemap.xml')
  ->with('response')->begin()
    ->isStatusCode('200')
    ->isValid()
    ->matches('/http:\/\//')
    ->matches('/sample-page/')
  ->end()
  ->with('request')->begin()
    ->isParameter('module', 'sympal_default')
    ->isParameter('action', 'sitemap')
  ->end()
;