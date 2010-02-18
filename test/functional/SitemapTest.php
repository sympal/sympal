<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->
  get('/sitemap.xml')->
  with('response')->begin()->
    isStatusCode('200')->
    isValid()->
    matches('/http:\/\//')->
    matches('/sample-page/')->
  end()->
  with('request')->begin()->
    isParameter('module', 'sympal_default')->
    isParameter('action', 'sitemap')->
  end()
;