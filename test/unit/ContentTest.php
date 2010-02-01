8<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(17, new lime_output_color());

// Setup sample content record and menu item to test with
$user = new sfGuardUser();
$user->first_name = 'test';
$user->last_name = 'test';
$user->email_address = 'test@gmail.com';
$user->username = rand();
$user->password = 'test';
$user->save();

$content = sfSympalContent::createNew('sfSympalPage');
$content->slug = 'testing-this-out';
$content->date_published = date('Y-m-d', time() - 3600);
$content->CreatedBy = $user;
$content->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug('sympal');
$content->title = 'Testing this out';
$content->save();

$menuItem = new sfSympalMenuItem();
$menuItem->name = 'test';
$menuItem->RelatedContent = $content;
$menuItem->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug('sympal');
$menuItem->save();
$content->save();

// Test that $content was setup successfully
$t->is($content->sfSympalPage instanceof sfSympalPage, true, 'Test that content type was instantiated properly');
$t->is($content->sfSympalPage->title, 'Testing this out', 'Test that content type instance title was set from content record');

// Query for new content
$q = Doctrine_Core::getTable('sfSympalContent')
  ->getFullTypeQuery('sfSympalPage')
  ->andWhere('c.slug = ?', 'testing-this-out');

$content = $q->fetchOne();
$type = $content->getType();
$site = $content->getSite();

// Test type
$t->is($type->getName(), 'sfSympalPage', 'Test content type name');
$t->is($type->getLabel(), 'Page', 'Test content type label');

// Test site
$t->is($site->getTitle(), 'Sympal', 'Test site name');
$t->is($site->getSlug(), 'sympal', 'Test site slug');

$sfUser = sfContext::getInstance()->getUser();
$sfUser->signIn($user);
$sfUser->isEditMode(true);

// Refresh content
$content->refresh();

$content->publish();
$t->is(strtotime($content->date_published) > 0, true, 'Test content is published');

$content->unpublish();
$t->is(strtotime($content->date_published) > 0, false, 'Test content is unpublished');
$t->is(strtotime($menuItem->date_published) > 0, false, 'Test menu item is unpublished');

$t->is($content->getTitle(), 'Testing this out', 'Test getting content type instance title from content');
$t->is($content->getTemplateToRenderWith(), 'sympal_page/view', 'Test getTemplateToRenderWith()');
$t->is($content->getHeaderTitle(), 'Testing this out', 'Test getHeaderTitle()');
$t->is($content->getLayout(), null, 'Test getlayout()');
$t->is($content->getRoute(), '@page?slug=testing-this-out', 'Test getRoute()');

// Test getting and adding of slots
$t->is($content->getSlots()->count(), 0, 'Test we have 0 slots');

get_sympal_content_slot($content, 'title', 'Text');
get_sympal_content_slot($content, 'body', 'Markdown');
get_sympal_content_slot($content, 'teaser', 'RawHtml');

$content->refresh(true);
$t->is($content->getSlots()->count(), 3, 'Test we have 3 slots');

get_sympal_content_slot($content, 'title', 'Text');
$t->is($content->getSlots()->count(), 3, 'Test we still have 3 slots');