<?php

$app = 'sympal';
$refresh_assets = true;
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6);
$assetNum = 3; // the number of test assets

$sync = new sfSympalAssetSynchronizer($configuration->getEventDispatcher());

$t->info('1 - Test that the synchronizer loads in the assets');
$assets = Doctrine_Core::getTable('sfSympalAsset')->findAll();
$t->is(count($assets), 0, 'There are 0 assets before the synchronizer is run');

$sync->run();

$assets = Doctrine_Core::getTable('sfSympalAsset')->findAll();
$t->is(count($assets), $assetNum, sprintf('There are %s assets after the synchronizer is run', $assetNum));

$t->info('2 - Test that the assets were loaded with the correct data');

check_asset_values($t, $assets[0], array(
  'name' => 'sympal info.txt',
  'path' => '',
  'slug' => 'sympal-info',
));

check_asset_values($t, $assets[1], array(
  'name' => 'sympalphp.png',
  'path' => '/screens',
  'slug' => 'screens-sympalphp',
));

check_asset_values($t, $assets[2], array(
  'name' => 'symfony-logo.gif',
  'path' => '/logos',
  'slug' => 'logos-symfony-logo',
));


$t->info('3 - Delete a file and rerun the synchronizer');
unlink(sfConfig::get('sf_upload_dir').'/logos/symfony-logo.gif');
$sync->run();

$assets = Doctrine_Core::getTable('sfSympalAsset')->findAll();
$t->is(count($assets), $assetNum - 1, sprintf('There are %s assets after the synchronizer is run', $assetNum - 1));

function check_asset_values(lime_test $t, sfSympalAsset $asset, $values)
{
  $savedValues = $asset->toArray();
  unset($savedValues['id']);

  $t->is($savedValues, $values, sprintf('The asset "%s" has the correct values', $values['name']));
}