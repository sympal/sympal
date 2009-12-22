<?php

$app = 'no_sympal';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->get('/')->
  with('request')->begin()->
    isParameter('module', 'test')->
    isParameter('action', 'index')->
  end()
;