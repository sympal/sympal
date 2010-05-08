<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(10);

chdir(sfConfig::get('sf_root_dir'));

function generatePlugin($name, $contentType, $t)
{
  global $configuration;

  $generate = new sfSympalPluginGenerateTask($configuration->getEventDispatcher(), new sfFormatter());
  $generate->run(array($name), array('--re-generate', '--no-confirmation', '--content-type='.$contentType));

  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.sfSympalPluginToolkit::getLongPluginName($name)), true, 'Test that the plugin was generated');
}

function downloadPlugin($name, $t)
{
  $manager = sfSympalPluginManager::getActionInstance($name, 'uninstall');
  $manager->uninstall(true);

  $manager = sfSympalPluginManager::getActionInstance($name, 'download');
  $manager->download();

  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.$name), true, 'Test that the plugin exists and was downloaded');
}

function deletePlugin($name, $t)
{
  $t->info('Deleting plugin ' . $name);
  $manager = sfSympalPluginManager::getActionInstance($name, 'delete');
  $manager->delete();
  
  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.$name), false, 'The plugin was deleted entirely');
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/model/doctrine/'.$name), false, 'The model files were deleted');
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/form/doctrine/'.$name), false, 'The form files were deleted');
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/filter/doctrine/'.$name), false, 'The filter files were deleted');
}

$t->info('1 - Download a plugin and see that it exists');
downloadPlugin('sfSympalObjectReplacerPlugin', $t);

$t->info('2 - Generate a content type plugin, see that it exists');
generatePlugin('Event', 'Event', $t);

$t->info('3 - Delete both plugins');
deletePlugin('sfSympalObjectReplacerPlugin', $t);
deletePlugin('sfSympalEventPlugin', $t);