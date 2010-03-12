<?php

$app = 'sympal';
$refresh_assets = true;
require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(21);

$defaultThumbWidth = 64;
$defaultThumbHeight = 64;

// initialize some asset objects
$sync = new sfSympalAssetSynchronizer($configuration->getEventDispatcher());
$sync->run();

$asset = Doctrine_Core::getTable('sfSympalAsset')->findOneBySlug('screens-sympalphp')->getAssetObject();

$t->info('1 - Basic width & height tests');
$t->is($asset->getWidth(), 1024, '->getWidth() returns the image width');
$t->is($asset->getHeight(), 590, '->getHeight() returns the image height');
$t->is($asset->isImage(), true, '->isImage() return true');

$t->info('2 - Basic tests on the default thumbnail');
$defaultThumbPath = $asset->getPathDirectory().'/.thumbnails/'.$defaultThumbWidth.'/'.$defaultThumbHeight.'/fit/sympalphp.png';

$allThumbnails = $asset->getAllThumbnails();
$t->is(count($allThumbnails), 1, '->getThumbnails() - returns 1 thumbnail, the default generated');
$t->is($allThumbnails[0]->getPath(), $defaultThumbPath);

$thumbnail = $asset->getThumbnail();
$t->is($thumbnail->getWidth(), $defaultThumbWidth, 'The default thumbnail has the right width');
$t->is($thumbnail->getHeight(), $defaultThumbHeight, 'The default thumbnail has the right height');

$t->is($thumbnail->getPath(), $defaultThumbPath, '->getPath() return the correct path to the thumbnail');
$t->is(file_exists($defaultThumbPath), true, 'Double check that the default thumbnail file exists');

$t->info('3 - Create a new thumbnail');
$newThumb = $asset->getThumbnail(300, 200, 'center');
$t->is($newThumb->exists(), true, 'The new thumb exists');
$t->is($newThumb->getWidth(), '300', 'The new thumbnail has width 300');
$t->is($newThumb->getHeight(), '200', 'The new thumbnail has height 200');

$path = $asset->getPathDirectory().'/.thumbnails/300/200/center/sympalphp.png';
$t->is($newThumb->getPath(), $path, '->getPath() return the correct path to the thumbnail');
$t->is(file_exists($path), true, 'Double check that the default thumbnail file exists');

$t->is(count($asset->getAllThumbnails()), 2, '->getAllThumbnails() now returns 2 objects');

$t->info('4 - Perform a move operation');

$oldPath = $asset->getPath();
$newPath = sfConfig::get('sf_upload_dir').'/moved/sympalphp.png';
$asset->move($newPath);

$allThumbnails = $asset->getAllThumbnails();
$t->is(count($allThumbnails), 2, 'There are still two thumbnails');
$t->is($allThumbnails[0]->exists(), true, 'The thumbnail exists');
$t->is(file_exists(dirname($newPath).'/.thumbnails/300/200/center/sympalphp.png'), true, 'The new file does indeed exist');
$t->is(file_exists(dirname($oldPath).'/.thumbnails/300/200/center/sympalphp.png'), false, 'The old file does not exist');


$t->info('5 - Perform a delete');
$oldThumbnailPath = $allThumbnails[0]->getPath();
$asset->delete();
$t->is(count($asset->getAllThumbnails()), 0, '->getAllThumbnails() now returns an empty array');
$t->is(file_exists($oldThumbnailPath), false, 'The old thumbnail file no longer exists');