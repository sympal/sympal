<?php
/*
 * Functional test for an application where sympal is disabled
 */

$app = 'no_sympal';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

// Purposefully NOT sfSympalTestFunctional, which is sympal-specific
$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/')
  ->with('request')->begin()
    ->isParameter('module', 'test')
    ->isParameter('action', 'index')
  ->end()
;