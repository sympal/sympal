<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(17);

$pluginConfig = $configuration->getPluginConfiguration('sfSympalPlugin');
$sympalContext = $pluginConfig->getSympalConfiguration()->getSympalContext();
$dir = $pluginConfig->getRootDir();

// Default sympal theme
$theme = $sympalContext->getThemeObject('sympal');

$t->is($theme->getLayoutPath(), $dir.'/templates/sympal.php');
$t->is($theme->getStylesheets(), array('/sfSympalPlugin/css/sympal.css'));
$t->is($theme->getJavascripts(), array());
$t->is($theme->getName(), 'sympal');
$t->is($theme->getConfiguration() instanceof sfSympalThemeConfiguration, true);

// Theme from another plugin
$theme = $sympalContext->getThemeObject('test_theme');

$t->is($theme->getLayoutPath(), $dir.'/test/fixtures/project/plugins/sfSympalThemeTestPlugin/templates/test_theme.php');
$t->is($theme->getStylesheets(), array('/sfSympalThemeTestPlugin/css/test_theme.css'));
$t->is($theme->getJavascripts(), array());

// Theme from application
$theme = $sympalContext->getThemeObject('test');

$t->is($theme->getLayoutPath(), $dir.'/test/fixtures/project/apps/sympal/templates/test.php');
$t->is($theme->getStylesheets(), array('test'));
$t->is($theme->getJavascripts(), array());
$t->is(count($sympalContext->getThemeObjects()), 4);
$theme = $sympalContext->getThemeObject('test');
$t->is(count($sympalContext->getThemeObjects()), 4);
try {
  $theme = $sympalContext->getThemeObject('test2');
  $t->fail('Should have thrown exception');
} catch (Exception $e) {
  $t->pass();
}
$t->is(count($sympalContext->getThemeObjects()), 4);

$adminTheme = $sympalContext->getThemeObject('admin');
$t->is($adminTheme->getStylesheets(), array(
  '/sfSympalPlugin/fancybox/jquery.fancybox.css',
  '/sfSympalAdminPlugin/css/global.css',
  '/sfSympalAdminPlugin/css/default.css',
  '/sfSympalAdminPlugin/css/admin.css'
));
$t->is($adminTheme->getJavascripts(), array(
  '/sfSympalPlugin/js/jQuery.cookie.js',
  '/sfSympalPlugin/fancybox/jquery.fancybox.js',
  '/sfSympalAdminPlugin/js/admin.js',
  '/sfSympalPlugin/js/shortcuts.js'
));