<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(14, new lime_output_color());

$user = sfContext::getInstance()->getUser();
$content = Doctrine::getTable('Content')
  ->getTypeQuery('Page')
  ->andWhere('c.slug = ?', 'home')
  ->fetchOne();

$t->is($user->hasAccessToContent($content), true);

$content->Permissions[] = Doctrine::getTable('Permission')->findOneByName('ManageContent');
$content->save();

$admin = Doctrine::getTable('User')->findOneByIsSuperAdmin(1);
$user->signIn($admin);

$t->is($user->hasAccessToContent($content), true);
$t->is($user->isEditMode(), true);

$user->toggleEditMode();
$t->is($user->isEditMode(), false);

$user->toggleEditMode();
$t->is($user->isEditMode(), true);

$user->obtainContentLock($content);

$t->is($content->locked_by, $admin->id);
$t->is($user->getOpenContentLock()->id, $content->id);
$user->releaseOpenLock();

$content->refresh();
$t->is($content->locked_by, null);

$t->is($user->isSuperAdmin(), true);
$t->is($user->isAnonymous(), false);
$t->is($user->hasCredential('BlahSomethingFake'), true);
$t->is($user->getSympalUser()->id, $admin->id);
$t->is((string) $user, 'Sympal Admin');
$t->is($user->checkPassword('admin'), true);