<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(9);

$user = sfContext::getInstance()->getUser();
$content = Doctrine_Core::getTable('sfSympalContent')
  ->getTypeQuery('sfSympalPage')
  ->andWhere('c.slug = ?', 'home')
  ->fetchOne();

$t->is($user->hasAccessToViewContent($content), true, '->hasAccessToViewContent($homepage) returns true for the user');

$group = new sfGuardGroup();
$group->name = 'SpecialGroup';
$group->save();

$content->Groups[] = $group;
$content->save();

$admin = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->findOneByIsSuperAdmin(1);
$user->signIn($admin);

$t->is($user->hasAccessToViewContent($content), true, 'Super admin user has access to view ocntent in SpecialGroup');
$t->is($user->isEditMode(), true, '->isEditMode() returns true because the user has access to edit content');

$t->is($user->isSuperAdmin(), true, '->isSuperAdmin() returns true for the user');
$t->is($user->isAnonymous(), false, '->isAnonymous() returns false for the user');
$t->is($user->hasCredential('BlahSomethingFake'), true, '->hasCredential() on a non-existent credential returns true for super admin');
$t->is($user->getGuardUser()->id, $admin->id, 'The user\'s guard user matches the signed in user');
$t->is((string) $user, 'Sympal Admin (admin)', '->__toString() on the user returns $name ($username)');
$t->is($user->checkPassword('admin'), true, '->checkPassword() returns true on the correct password');