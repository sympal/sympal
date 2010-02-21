<?php

/**
 * PluginsfGuardGroupForm tests.
 */
include dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php';

$databaseManager = new sfDatabaseManager($configuration);

$t = new lime_test(3);

class TestsfGuardGroupForm extends PluginsfGuardGroupForm
{
  public function configure()
  {
  }
}

// ->__construct()
$t->diag('->__construct()');

$form = new TestsfGuardGroupForm();
$t->ok(!isset($form['sf_guard_user_group_list']), '->__construct() removes fields');
$t->ok(!isset($form['created_at']), '->__construct() removes fields');
$t->ok(!isset($form['updated_at']), '->__construct() removes fields');