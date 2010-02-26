<?php

/**
 * PluginsfGuardUserForm tests.
 */
include dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php';

$databaseManager = new sfDatabaseManager($configuration);

$t = new lime_test(0);

class TestsfGuardUserForm extends PluginsfGuardUserForm
{
  public function configure()
  {
  }
}

// ->__construct()
$t->diag('->__construct()');

$form = new TestsfGuardUserForm();
