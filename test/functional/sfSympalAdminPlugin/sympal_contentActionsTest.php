<?php

/**
 * Testing the sympal_content actions class
 */

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signinAsAdmin();

$types = Doctrine_Core::getTable('sfSympalContentType')->getAllContentTypes();
$type = $types[0];
$content = new sfSympalContent();
$content->setType($type);
$contentForm = new sfSympalContentForm($content);

$browser->info('1 - Test the new action')
  
  ->info('  1.1 - Going to the new action directly displays a menu of the content types')
  
  ->get('/admin/content/manage/new')
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'new')
  ->end()
  
  ->isForwardedTo('sympal_content', 'chooseNewType')
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Add new content/')
    ->checkElement('ul.new-content-type li', 3)
  ->end()
  
  ->info(sprintf('  1.2 - Click on the %s new content', $type->name))
  ->click('Create')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_content')
    ->isParameter('action', 'create_type')
    ->isParameter('type', $type->id)
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', 'Create New '.$type->label)
    ->checkForm($contentForm)
  ->end()
;