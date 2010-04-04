<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(25);

$configuration->loadHelpers(array('I18N'));

function installPlugin($name, $t)
{
  // Install the plugin
  $manager = sfSympalPluginManager::getActionInstance($name, 'install');
  $manager->install();

  // Check that content type was created
  $contentTypeName = $manager->getContentTypeForPlugin($name);
  $contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($contentTypeName);
  $t->is($contentType['name'], $contentTypeName, 'Test content type name set');
  $t->is($contentType['label'], sfInflector::humanize(sfInflector::tableize(str_replace('sfSympal', null, $contentTypeName))), 'Test content type label set');
  $t->is($contentType['plugin_name'], $name, 'Test content type plugin name set');

  $contentList = Doctrine_Core::getTable('sfSympalContentList')->findOneByContentTypeId($contentType['id']);
  $t->is($contentList instanceof sfSympalContentList, true, 'Test sample content list was created.');

  $menuItem = Doctrine_Core::getTable('sfSympalMenuItem')->findOneByContentId($contentList['content_id']);
  $t->is($menuItem instanceof sfSympalMenuItem, true, 'Test menu item to sample content list was created.');
}

function uninstallPlugin($name, $t, $delete = true)
{
  $manager = sfSympalPluginManager::getActionInstance($name, 'uninstall');
  $manager->uninstall($delete);

  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.$name), !$delete, 'Test plugin was was deleted');
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/model/doctrine/'.$name), !$delete, 'Test plugin models were deleted');
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/form/doctrine/'.$name), !$delete, 'Test plugin forms were deleted');
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/filter/doctrine/'.$name), !$delete, 'Test plugin form filters were deleted');
  $t->is(file_exists(sfConfig::get('sf_web_dir').'/'.$name), !$delete, 'Test plugin assets symlink was removed');
}

$t->info('1 - Uninstalling sfSympalBlogPlugin...');
uninstallPlugin('sfSympalBlogPlugin', $t, false);
$t->info('2 - Installing sfSympalBlogPlugin...');
installPlugin('sfSympalBlogPlugin', $t);

$t->info('3 - Installing sfSympalEventPlugin');
installPlugin('sfSympalEventPlugin', $t);

$t->info('4 - Uninstalling sfSympalEventPlugin...');
uninstallPlugin('sfSympalEventPlugin', $t);

$t->info('5 - Uninstalling sfSympalObjectReplacerPlugin');
uninstallPlugin('sfSympalObjectReplacerPlugin', $t);