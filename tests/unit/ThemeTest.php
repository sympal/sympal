<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

$dir = $configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir();

// Default sympal theme
$theme = new sfSympalTheme('sympal');

$t->is($theme->getLayoutPath(), $dir.'/templates/sympal.php');
$t->is($theme->getCssPath(), '/sfSympalPlugin/css/sympal');

// Theme from another plugin
$theme = new sfSympalTheme('test_theme');

$t->is($theme->getLayoutPath(), $dir.'/tests/fixtures/project/plugins/sfSympalThemeTestPlugin/templates/test_theme.php');
$t->is($theme->getCssPath(), '/sfSympalThemeTestPlugin/css/test_theme.css');

// Theme from application
$theme = new sfSympalTheme('test');

$t->is($theme->getLayoutPath(), $dir.'/tests/fixtures/project/apps/sympal/templates/test.php');
$t->is($theme->getCssPath(), 'test');