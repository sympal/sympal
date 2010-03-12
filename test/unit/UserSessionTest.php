<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(9);

$user = sfContext::getInstance()->getUser();
$content = Doctrine_Core::getTable('sfSympalContent')
  ->getTypeQuery('sfSympalPage')
  ->andWhere('c.slug = ?', 'home')
  ->fetchOne();

$t->is($user->hasAccessToViewContent($content), true);

$group = new sfGuardGroup();
$group->name = 'SpecialGroup';
$group->save();

$content->Groups[] = $group;
$content->save();

$admin = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->findOneByIsSuperAdmin(1);
$user->signIn($admin);

$t->is($user->hasAccessToViewContent($content), true);
$t->is($user->isEditMode(), true);

$t->is($user->isSuperAdmin(), true);
$t->is($user->isAnonymous(), false);
$t->is($user->hasCredential('BlahSomethingFake'), true);
$t->is($user->getGuardUser()->id, $admin->id);
$t->is((string) $user, 'Sympal Admin (admin)');
$t->is($user->checkPassword('admin'), true);