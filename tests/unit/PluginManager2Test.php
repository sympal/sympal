<?php
$database = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(20, new lime_output_color());

function installPlugin($name, $t)
{
  $manager = sfSympalPluginManager::getActionInstance($name, 'install');
  $manager->install();

  $contentTypeName = $manager->getContentTypeForPlugin($name);
  $contentType = Doctrine::getTable('ContentType')->findOneByName($contentTypeName);
  $t->is($contentType['name'], $contentTypeName);
  $t->is($contentType['label'], $contentTypeName);
  $t->is($contentType['plugin_name'], $name);

  $short = sfSympalPluginToolkit::getShortPluginName($name);
  $menuItem = Doctrine::getTable('MenuItem')->findOneByName($short);
  $t->is($menuItem['name'], $short);

  $t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home > '.$short);
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
uninstallPlugin('sfSympalBlogPlugin', $t);

installPlugin('sfSympalEventPlugin', $t);
uninstallPlugin('sfSympalEventPlugin', $t);