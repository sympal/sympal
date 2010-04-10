<?php

$app = 'sympal';
$refresh_assets = true;
require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(34);

// initialize some asset objects
$sync = new sfSympalAssetSynchronizer($configuration->getEventDispatcher());
$sync->run();

$asset = get_test_asset_object();
$asset2 = get_test_asset_object('screens-sympalphp');

$t->info('1 - Run some basic functions on the asset');
$t->isnt($asset, null, 'An asset object for the "sympal info.txt" asset was found');
$t->is($asset->isImage(), false, '->isImage() returns false');
$t->is($asset->getType(), 'text', '->getType() returns "text"');
$t->is($asset->exists(), true, '->exists() returns true as the file does exist');
$t->is($asset->getTypeFromExtension(), 'text', '->getTypeFromExtension() returns text');
$t->is($asset->getIcon(), '/sfSympalAssetsPlugin/images/icons/txt.png', '->getIcon() returns the txt.png icon');

$t->is($asset->getExtension(), 'txt', '->getExtension() returns .txt');
$t->is($asset->getPath(), sfConfig::get('sf_upload_dir').'/sympal info.txt', '->getPath() returns the correct path');
$t->is($asset->getRelativePath(), '/sympal info.txt', '->getRelativePath() returns the web url for the asset');
$t->is($asset->getRelativePathDirectory(), '', '->getRelativePathDirectory() returns a blank string');
$t->is($asset->getFilePath(), '/sympal info.txt', '->getFilePath() returns /sympal info.txt');

$t->is($asset2->getPath(), sfConfig::get('sf_upload_dir').'/screens/sympalphp.png', '->getPath() returns the correct path');
$t->is($asset2->getRelativePath(), '/screens/sympalphp.png', '->getRelativePath() returns the web url for the asset');
$t->is($asset2->getRelativePathDirectory(), '/screens', '->getRelativePathDirectory() returns "/screens"');
$t->is($asset2->getFilePath(), '/screens/sympalphp.png', '->getFilePath() returns /sympal info.txt');

$t->like($asset2->getUrl(), '/uploads\/screens\/sympalphp\.png/', '->getFilePath() contains /uploads/screens/sympalphp.png');
$t->like($asset2->getUrl(), '/http\:\/\//', '->getFilePath() contains http://');

$t->is($asset->getName(), 'sympal info.txt', '->getName() return "sympal info.txt"');
$t->is($asset2->getSize(), '275', '->getSize() returns 275 for a 274.7kb file');

$doctrineAsset = $asset->getDoctrineAsset();
$t->is($doctrineAsset->name, $asset->getName(), '->getDoctrineAsset() returns the correct asset');

$t->info('  1.1 - Play with the getOriginal() method');
$original = $asset->getOriginal();
$t->is($original->getPath(), $asset->getPathDirectory().'/.originals/'.$asset->getName(), 'An original copy of the file exists in the .originals directory');

$t->info('  1.1.1 Unlink the original asset and see if we can recreate it');
$asset = get_test_asset_object(); // resets the original
$original = $asset->getOriginal(false);
$t->is($original->getPath(), $asset->getPathDirectory().'/.originals/'.$asset->getName(), 'The original copy file exists, so it returns just fine');

unlink($original->getPath());
$asset = get_test_asset_object(); // resets the original
$original = $asset->getOriginal(false);
$t->is($original, null, '->getOriginal() return null if there is no original and you don\'t request to make a new one');

$asset = get_test_asset_object(); // resets the original
$original = $asset->getOriginal();
$t->is($original->getPath(), $asset->getPathDirectory().'/.originals/'.$asset->getName(), 'The original exists since we\'ve requested it to get recreated');


$t->info('2 - Perform a move operation');

$oldPath = $asset2->getPath();
$newPath = sfConfig::get('sf_upload_dir').'/moved/sympalphp.png';
$asset2->move($newPath);

$t->is($asset2->getPath(), $newPath, '->getPath() now returns the new path');
$t->is($asset2->exists(), true, '->exists returns true');
$t->is(file_exists($oldPath), false, 'The old file path does not exist');

$originalPath = dirname($newPath).'/.originals/sympalphp.png';
$t->is($asset2->getOriginal()->getPath(), $originalPath, '->getOriginal() has the correct new path');
$t->is($asset2->getOriginal()->exists(), true, '->getOriginal() exists');
$t->is(file_exists(dirname($oldPath).'/.originals/sympalphp.png'), false, 'The old original file path does not exist');


$t->info('3 - Perform a delete operation');
$oldPath = $asset2->getPath();
$asset2->delete();
$t->is($asset2->exists(), false, '->exists() returns false');
$t->is(file_exists($oldPath), false, 'The old file path does not exist');
$t->is(file_exists(dirname($oldPath).'/.originals/sympal info.txt'), false, 'The old original file path does not exist');


$t->info('4 - Test the render() method');

// a test rendering class
class testRenderer
{
  public function __construct(sfSympalAssetObject $asset, $options)
  {
  }

  public function render()
  {
    return 'rendered';
  }
}

$t->is($asset->render(array('renderer' => 'testRenderer')), 'rendered', 'Passing a "renderer" options uses a different rendering class');

// linked_thumbnail


function get_test_asset_object($slug = 'sympal-info')
{
  // make sure it's free, so we get a fresh object
  $record = Doctrine_Core::getTable('sfSympalAsset')->findOneBySlug($slug);
  $record->free();
  
  return Doctrine_Core::getTable('sfSympalAsset')->findOneBySlug($slug)->getAssetObject();
}