<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/functional.php');

$browser = new sfSympalTestFunctional(new sfBrowser());
$browser->signinAsAdmin();

refresh_assets();
$browser->info('Synchronizing the test assets...');
$sync = new sfSympalAssetSynchronizer($configuration->getEventDispatcher());
$sync->run();

$asset = Doctrine_Query::create()->from('sfSympalAsset a')->fetchOne();

$browser->info('1 - Browse around the assets library')
  ->get('/admin/assets')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_assets')
    ->isParameter('action', 'index')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Sympal Assets Manager/')
    ->info('  1.1 - There should be 1 root asset and 2 subdirectories')
    ->checkElement('.asset', 1)
    ->checkElement('.folder', 2)
  ->end()
  
  ->info('  1.2 - Click to go into the /screens directory')
  ->click('li.folder a')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_assets')
    ->isParameter('action', 'index')
    ->isParameter('dir', urlencode('/screens'))
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->info('  1.3 - There should be one asset in this directory')
    ->checkElement('.asset', 1)

    ->info('  1.4 - Check some information on the asset')
    ->checkElement('.asset .size', '/275 Kb/')
    ->checkElement('.asset .dimensions', '/1024x590/')
    ->checkElement('.asset .embed_code', '/[asset:screens-sympalphp]/')
  ->end()
  
  ->info('  1.5 - Click to edit the asset')
  ->click('.asset a.edit')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_assets')
    ->isParameter('action', 'edit_asset')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Editing Asset "sympalphp.png"/')
  ->end()
  
  ->info('  1.6 - Goto an edit of an asset just as a sanity check')
  
  ->get('/admin/assets/edit/asset/'.$asset->id)
  
  ->with('response')->begin()
    ->isStatusCode(200)
  ->end()
;

$browser->info('2 - Test the asset selector')
  ->get('/assets/select')
  
  ->with('request')->begin()
    ->isParameter('module', 'sympal_assets')
    ->isParameter('action', 'select')
  ->end()
  
  ->with('response')->begin()
    ->isStatusCode(200)
    ->checkElement('h1', '/Asset Browser/')
  ->end()
  
  // todo, click all the links
;