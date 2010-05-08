<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signinAsAdmin();

$browser->info('1 - Goto the links chooser and examine it')
  ->get('/editor/links')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_editor')
    ->isParameter('action', 'links')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->info('  1.1 - The 3 different content types show up in the #content_types div')
    ->checkElement('#content_types li', 3)
    ->info('  1.2 - Check for the #links_chooser div')
    ->checkElement('#links_chooser', true)
  ->end()
;