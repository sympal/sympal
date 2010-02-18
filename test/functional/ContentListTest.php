<?php

require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());

$browser->
  get('/lists/sample-content-list')->
  with('response')->begin()->
    isStatusCode(200)->
    isValid()->
  end()->
  with('request')->begin()->
    isFormat('html')->
  end()
;

$browser->
  get('/lists/sample-content-list.rss')->
  with('response')->begin()->
    isStatusCode(200)->
    isValid()->
  end()->
  with('request')->begin()->
    isFormat('rss')->
  end()
;