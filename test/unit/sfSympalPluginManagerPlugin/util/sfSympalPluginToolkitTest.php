<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(2);

$t->info('1 - Test ::getPluginPath()');
$t->is(sfSympalPluginToolkit::getPluginPath('fakePlugin'), false, '::getPluginPath() returns false for a non-existent plugin');
$t->is(sfSympalPluginToolkit::getPluginPath('sfSympalBlogPlugin'), sfConfig::get('sf_plugins_dir').'/sfSympalBlogPlugin', '::getPluginPath() returns the absolute path to a real plugin');
