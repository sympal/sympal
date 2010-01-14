<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(18, new lime_output_color());

$configuration->loadHelpers(array('I18N'));

function installPlugin($name, $t)
{
  $manager = sfSympalPluginManager::getActionInstance($name, 'install');
  $manager->install();

  $contentTypeName = $manager->getContentTypeForPlugin($name);
  $contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($contentTypeName);
  $t->is($contentType['name'], $contentTypeName);
  $t->is($contentType['label'], sfInflector::humanize(sfInflector::tableize(str_replace('sfSympal', null, $contentTypeName))));
  $t->is($contentType['plugin_name'], $name);

  $menuItem = Doctrine_Core::getTable('sfSympalMenuItem')->findOneByName($contentTypeName);
  $t->is($menuItem['name'], $contentTypeName);
}

function uninstallPlugin($name, $t)
{
  $manager = sfSympalPluginManager::getActionInstance($name, 'uninstall');
  $manager->uninstall(true);

  $t->is(file_exists(sfConfig::get('sf_plugins_dir').'/'.$name), false);
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/model/doctrine/'.$name), false);
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/form/doctrine/'.$name), false);
  $t->is(file_exists(sfConfig::get('sf_lib_dir').'/filter/doctrine/'.$name), false);
  $t->is(file_exists(sfConfig::get('sf_web_dir').'/'.$name), false);
}

installPlugin('sfSympalBlogPlugin', $t);

installPlugin('sfSympalEventPlugin', $t);
uninstallPlugin('sfSympalEventPlugin', $t);