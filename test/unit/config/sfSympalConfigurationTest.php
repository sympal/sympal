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
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(20);

$sympalPluginConfiguration = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin');
$sympalConfiguration = $sympalPluginConfiguration->getSympalConfiguration();

$contentTemplates = $sympalConfiguration->getContentTemplates('page'); 
$t->is(isset($contentTemplates['default_view']), true, '->getContentTemplates() returns default_view for page'); 
$t->is(isset($contentTemplates['register']), true, '->getContentTemplates() returns register for page'); 
 
$plugins = $sympalConfiguration->getPlugins(); 
$t->is(is_array($plugins), true, '->getPlugins() returns array'); 
$t->is(in_array('sfSympalBlogPlugin', $plugins), true, '->getPlugins() includes sfSympalBlogPlugin'); 
 
$pluginPaths = $sympalConfiguration->getPluginPaths(); 
$t->is(is_array($pluginPaths), true, '->getPluginPaths() returns array'); 
$t->is(isset($pluginPaths['sfSympalBlogPlugin']), true, '->getPluginPaths() includes sfSympalBlogPlugin'); 
$t->is($pluginPaths['sfSympalBlogPlugin'], sfConfig::get('sf_plugins_dir').'/sfSympalBlogPlugin', '->getPluginPaths() returns correct path as value of array');

$corePlugins = array(
  'sfDoctrineGuardPlugin',
  'sfFormExtraPlugin',
  'sfTaskExtraPlugin',
  'sfFeed2Plugin',
  'sfWebBrowserPlugin',
  'sfJqueryReloadedPlugin',
  'sfImageTransformPlugin',
  'sfSympalCMFPlugin',
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
  'sfSympalSearchPlugin',
  'sfSympalThemePlugin',
  'sfSympalMinifyPlugin',
);

$t->is($sympalConfiguration->getCorePlugins(), $corePlugins, '->getCorePlugins() returns the correct array');

$installedPlugins = array_values($sympalConfiguration->getDownloadedPlugins());
sort($installedPlugins); // sort the plugins - don't depend on file system to return with consistent order
$t->is($installedPlugins, array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalThemeTestPlugin'
), '->getDownloadedPlugins() returns the correct array of installed, non-core plugins');

$addonPlugins = $sympalConfiguration->getDownloadablePlugins();
$t->is(in_array('sfSympalBlogPlugin', $addonPlugins), true, '->getDownloadablePlugins() returns an array which includes sfSympalBlogPlugin');
$t->is(in_array('sfSympalJwageThemePlugin', $addonPlugins), true, '->getDownloadablePlugins() returns an array which includes sfSympalJwageThemePlugin');

$otherPlugins = array_values($sympalConfiguration->getDownloadedPlugins());
sort($otherPlugins); // sort the plugins - don't depend on file system to return with consistent order
$t->is($otherPlugins, array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalThemeTestPlugin'
), '->getDownloadedPlugins() returns the correct array of installed, non-core plugins (equivalent to getDownloadedPlugins())');


$pluginPaths = $sympalConfiguration->getPluginPaths();
$t->is($pluginPaths['sfSympalPlugin'], $sympalPluginConfiguration->getRootDir(), '->getRootDir() returns the root path to sfSympalPlugin');

$modules = $sympalConfiguration->getModules();
$t->is(in_array('sympal_content_renderer', $modules), true, '->getModules() returns an array with sympal_content_renderer as an entry');

$layouts = $sympalConfiguration->getLayouts();
$t->is(in_array('sympal', $layouts), true, '->getLayouts() returns an array with "sympal" as one of its entries');

$contentTypePlugins = $sympalConfiguration->getContentTypePlugins(); 
$t->is($contentTypePlugins, array( 
  'sfSympalBlogPlugin', 
  'sfSympalPagesPlugin', 
  'sfSympalContentListPlugin' 
), '->getContentTypePlugins() returns the correct array of plugins with content typed defined'); 
 
$allManageablePlugins = $sympalConfiguration->getAllManageablePlugins(); 
$t->is(in_array('sfSympalBlogPlugin', $allManageablePlugins), true, '->getAllManageablePlugins() returns the correct array of plugins');

$plugins = $sympalConfiguration->getProjectConfiguration()->getPlugins();
// The plugins array may have gaps in its indexes. Doing an array_merge on nothing resets the indexes
$plugins = array_merge($plugins, array());

$t->is($plugins[0], 'sfDoctrinePlugin', 'Test sfDoctrinePlugin is loaded first');
$t->is($plugins[1], 'sfSympalPlugin', 'Test sfSympalPlugin is loaded second');
$t->is(in_array('sfSympalBlogPlugin', $plugins), true, 'Test that additional downloaded plugins are loaded');