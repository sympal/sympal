<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(11, new lime_output_color());

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
  'sfSympalFrontendEditorPlugin',
  'sfSympalAssetsPlugin',
);

$t->is($sympalConfiguration->getRequiredPlugins(), $requiredPlugins);

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
  'sfSympalFrontendEditorPlugin',
  'sfSympalAssetsPlugin'
);

$t->is($sympalConfiguration->getCorePlugins(), $corePlugins);

$t->is($sympalConfiguration->getInstalledPlugins(), array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalThemeTestPlugin'
));

$addonPlugins = $sympalConfiguration->getAddonPlugins();
$t->is(in_array('sfSympalBlogPlugin', $addonPlugins), true);
$t->is(in_array('sfSympalJwageThemePlugin', $addonPlugins), true);

$t->is($sympalConfiguration->getOtherPlugins(), array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalThemeTestPlugin'
));

$all = array(
  'sfSympalBlogPlugin',
  'sfSympalCommentsPlugin',
  'sfSympalDoctrineThemePlugin',
  'sfSympalJwageThemePlugin',
  'sfSympalOpenAuthPlugin',
  'sfSympalSlideshowPlugin',
  'sfSympalSocialFollowPlugin',
  'sfSympalTagsPlugin',
);

$t->is(in_array('sfSympalBlogPlugin', $all), true);
$t->is(in_array('sfSympalJwageThemePlugin', $all), true);

$pluginPaths = $sympalConfiguration->getPluginPaths();
$t->is($pluginPaths['sfSympalPlugin'], $sympalPluginConfiguration->getRootDir());

$modules = $sympalConfiguration->getModules();
$t->is(in_array('sympal_content_renderer', $modules), true);

$layouts = $sympalConfiguration->getLayouts();
$t->is(in_array('sympal', $layouts), true);