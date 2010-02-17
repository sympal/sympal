<?php

/**
 * Unit test for the sfSympalConfiguration class
 * 
 * @package     
 * @subpackage  
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-02-06
 * @version     svn:$Id$ $Author$
 */

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

$sympalPluginConfiguration = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin');
$sympalConfiguration = $sympalPluginConfiguration->getSympalConfiguration();

$requiredPlugins = array(
  'sfSympalPlugin',
  'sfSympalUserPlugin',
  'sfFormExtraPlugin',
  'sfDoctrineGuardPlugin',
  'sfTaskExtraPlugin',
  'sfFeed2Plugin',
  'sfWebBrowserPlugin',
  'sfJqueryReloadedPlugin',
  'sfThumbnailPlugin',
  'sfImageTransformPlugin',
  'sfSympalMenuPlugin',
  'sfSympalPluginManagerPlugin',
  'sfSympalPagesPlugin',
  'sfSympalContentListPlugin',
  'sfSympalDataGridPlugin',
  'sfSympalInstallPlugin',
  'sfSympalUpgradePlugin',
  'sfSympalRenderingPlugin',
  'sfSympalAdminPlugin',
  'sfSympalEditorPlugin',
  'sfSympalAssetsPlugin',
  'sfSympalContentSyntaxPlugin',
  'sfSympalSearchPlugin'
);

$t->is($sympalConfiguration->getRequiredPlugins(), $requiredPlugins, '->getRequiredPlugins() returns the correct array');

$corePlugins = array(
  'sfDoctrineGuardPlugin',
  'sfFormExtraPlugin',
  'sfTaskExtraPlugin',
  'sfFeed2Plugin',
  'sfWebBrowserPlugin',
  'sfJqueryReloadedPlugin',
  'sfThumbnailPlugin',
  'sfImageTransformPlugin',
  'sfSympalMenuPlugin',
  'sfSympalPluginManagerPlugin',
  'sfSympalPagesPlugin',
  'sfSympalContentListPlugin',
  'sfSympalDataGridPlugin',
  'sfSympalUserPlugin',
  'sfSympalInstallPlugin',
  'sfSympalUpgradePlugin',
  'sfSympalRenderingPlugin',
  'sfSympalAdminPlugin',
  'sfSympalEditorPlugin',
  'sfSympalAssetsPlugin',
  'sfSympalContentSyntaxPlugin',
  'sfSympalSearchPlugin'
);

$t->is($sympalConfiguration->getCorePlugins(), $corePlugins, '->getCorePlugins() returns the correct array');

$installedPlugins = array_values($sympalConfiguration->getInstalledPlugins());
sort($installedPlugins); // sort the plugins - don't depend on file system to return with consistent order
$t->is($installedPlugins, array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalThemeTestPlugin'
), '->getInstalledPlugins() returns the correct array of installed, non-core plugins');

$addonPlugins = $sympalConfiguration->getAddonPlugins();
$t->is(in_array('sfSympalBlogPlugin', $addonPlugins), true, '->getAddonPlugins() returns an array which includes sfSympalBlogPlugin');
$t->is(in_array('sfSympalJwageThemePlugin', $addonPlugins), true, '->getAddonPlugins() returns an array which includes sfSympalJwageThemePlugin');

$otherPlugins = array_values($sympalConfiguration->getOtherPlugins());
sort($otherPlugins); // sort the plugins - don't depend on file system to return with consistent order
$t->is($otherPlugins, array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalThemeTestPlugin'
), '->getOtherPlugins() returns the correct array of installed, non-core plugins (equivalent to getInstalledPlugins())');


$pluginPaths = $sympalConfiguration->getPluginPaths();
$t->is($pluginPaths['sfSympalPlugin'], $sympalPluginConfiguration->getRootDir(), '->getRootDir() returns the root path to sfSympalPlugin');

$modules = $sympalConfiguration->getModules();
$t->is(in_array('sympal_content_renderer', $modules), true, '->getModules() returns an array with sympal_content_renderer as an entry');

$layouts = $sympalConfiguration->getLayouts();
$t->is(in_array('sympal', $layouts), true, '->getLayouts() returns an array with "sympal" as one of its entries');