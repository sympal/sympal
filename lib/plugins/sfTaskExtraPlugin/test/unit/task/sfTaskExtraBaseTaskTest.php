<?php

/**
 * sfTaskExtraBaseTask tests.
 */
include dirname(__FILE__).'/../../bootstrap/unit.php';

$task = new sfCacheClearTask($configuration->getEventDispatcher(), new sfFormatter());
$task->setConfiguration($configuration);

$t = new lime_test(5);

// ::doCheckPluginExists()
$t->diag('::doCheckPluginExists()');

try
{
  sfTaskExtraBaseTask::doCheckPluginExists($task, 'NonexistantPlugin');
  $t->fail('::doCheckPluginExists() throws an exception if the plugin does not exist');
}
catch (Exception $e)
{
  $t->pass('::doCheckPluginExists() throws an exception if the plugin does not exist');
}

try
{
  sfTaskExtraBaseTask::doCheckPluginExists($task, 'NonexistantPlugin', false);
  $t->pass('::doCheckPluginExists() does not throw an excpetion if a plugin does not exists and is passed false');
}
catch (Exception $e)
{
  $t->fail('::doCheckPluginExists() does not throw an excpetion if a plugin does not exists and is passed false');
  $t->diag('    '.$e->getMessage());
}

try
{
  sfTaskExtraBaseTask::doCheckPluginExists($task, 'StandardPlugin');
  $t->pass('::doCheckPluginExists() does not throw an exception if a plugin exists');
}
catch (Exception $e)
{
  $t->fail('::doCheckPluginExists() does not throw an exception if a plugin exists');
  $t->diag('    '.$e->getMessage());
}

try
{
  sfTaskExtraBaseTask::doCheckPluginExists($task, 'StandardPlugin', false);
  $t->fail('::doCheckPluginExists() throws an exception if a plugin exists and is passed false');
}
catch (Exception $e)
{
  $t->pass('::doCheckPluginExists() throws an exception if a plugin exists and is passed false');
}

try
{
  sfTaskExtraBaseTask::doCheckPluginExists($task, 'SpecialPlugin');
  $t->pass('::doCheckPluginExists() does not throw a plugin is enabled with a special path');
}
catch (Exception $e)
{
  $t->fail('::doCheckPluginExists() does not throw a plugin is enabled with a special path');
  $t->diag('    '.$e->getMessage());
}
