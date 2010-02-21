<?php

/**
 * PluginsfGuardPermissionForm tests.
 */
include dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php';

$databaseManager = new sfDatabaseManager($configuration);

$t = new lime_test(3);

class TestsfGuardPermissionForm extends PluginsfGuardPermissionForm
{
  public function configure()
  {
  }
}

// ->__construct()
$t->diag('->__construct()');

$form = new TestsfGuardPermissionForm();
$t->ok(isset($form['users_list']), '->__construct() does not remove users_list');
$t->ok(!isset($form['created_at']), '->__construct() removes created_at');
$t->ok(!isset($form['updated_at']), '->__construct() removes updated_at');