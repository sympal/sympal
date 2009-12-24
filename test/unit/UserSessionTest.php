<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(9, new lime_output_color());

$user = sfContext::getInstance()->getUser();
$content = Doctrine_Core::getTable('sfSympalContent')
  ->getTypeQuery('sfSympalPage')
  ->andWhere('c.slug = ?', 'home')
  ->fetchOne();

$t->is($user->hasAccessToContent($content), true);

$content->Permissions[] = Doctrine_Core::getTable('sfGuardPermission')->findOneByName('ManageContent');
$content->save();

$admin = Doctrine_Core::getTable('sfGuardUser')->findOneByIsSuperAdmin(1);
$user->signIn($admin);

$t->is($user->hasAccessToContent($content), true);
$t->is($user->isEditMode(), true);

$t->is($user->isSuperAdmin(), true);
$t->is($user->isAnonymous(), false);
$t->is($user->hasCredential('BlahSomethingFake'), true);
$t->is($user->getGuardUser()->id, $admin->id);
$t->is((string) $user, 'Sympal Admin (admin)');
$t->is($user->checkPassword('admin'), true);