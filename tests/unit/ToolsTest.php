<?php
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(29, new lime_output_color());

$plugins = sfSympalTools::getPlugins();
$t->is(isset($plugins['sfSympalPlugin']), true, 'Check sfSympalPlugin exists');
$t->is(is_dir($plugins['sfSympalPlugin']), true, 'Check value of plugin is path to directory containing plugin');

foreach ($plugins as $plugin)
{
  $t->is((substr($plugin, 0, 8) && substr($plugin, strlen($plugin) - 6, strlen($plugin))), true, 'Check all sympal plugins are prefixed with sfSympal and end with Plugin');
}

$modules = sfSympalTools::getModules();
foreach ($modules as $module)
{
  $t->is((substr($module, 0, 7) == 'sympal_'), true, 'Check all sympal modules are prefixed with sympal');
}