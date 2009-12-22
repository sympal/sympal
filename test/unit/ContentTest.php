8<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(40, new lime_output_color());

$user = new sfGuardUser();
$user->first_name = 'test';
$user->last_name = 'test';
$user->email_address = 'test@gmail.com';
$user->username = rand();
$user->password = 'test';
$user->save();

$content = sfSympalContent::createNew('sfSympalPage');
$content->slug = 'testing-this-out';
$content->is_published = true;
$content->date_published = date('Y-m-d', time() - 3600);
$content->CreatedBy = $user;
$content->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug('sympal');
$content->title = 'Testing this out';
$content->save();

$t->is($content->sfSympalPage instanceof sfSympalPage, true);
$t->is($content->sfSympalPage->title, 'Testing this out');

$menuItem = new sfSympalMenuItem();
$menuItem->name = 'test';
$menuItem->RelatedContent = $content;
$menuItem->is_published = true;
$menuItem->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug('sympal');
$menuItem->save();

$content->MasterMenuItem = $menuItem;
$content->save();

$q = Doctrine_Core::getTable('sfSympalContent')
  ->getFullTypeQuery('sfSympalPage')
  ->andWhere('c.slug = ?', 'testing-this-out');

$content = $q->fetchOne();

$t->is(isset($content['Type']), true);
$t->is($content['Type']['name'], 'sfSympalPage');
$t->is($content['Type']['label'], 'Page');
$t->is(isset($content['Site']), true);
$t->is($content['Site']['title'], 'Sympal');
$t->is($content['Site']['slug'], 'sympal');
$t->is(isset($content['sfSympalPage']), true);
$t->is($content['slug'], 'testing-this-out');

$sfUser = sfContext::getInstance()->getUser();
$sfUser->signIn($user);
$sfUser->isEditMode(true);

$content = $q->fetchOne();

$content->publish();
$t->is($content->is_published, true);
$t->is(strtotime($content->date_published) > 0, true);

$content->unpublish();
$t->is($content->is_published, false);
$t->is(strtotime($content->date_published) > 0, false);

$content->is_published = 1;
$content->date_published = new Doctrine_Expression('NOW()');
$content->save();
$content->free();

$content = $q->fetchOne();

$t->is($content->is_published, true);
$t->is(strtotime($content->date_published) > 0, true);

$menuItem = $content->getMainMenuItem();

$t->is($menuItem->name, 'test');

$page = $content->getRecord();
$t->is($page instanceof sfSympalPage, true);
$t->is($page->title, 'Testing this out');

$template = $content->getTemplateToRenderWith();
$t->is($template, 'sympal_page/view');

$t->is($content->getTitle(), 'Testing this out');
$t->is($content->getHeaderTitle(), 'Testing this out');

$t->is($content->getLayout(), 'sympal');
$t->is($content->getRoute(), '@page?slug=testing-this-out');

get_sympal_content_slot($content, 'title', 'Text');
get_sympal_content_slot($content, 'body', 'Markdown');
get_sympal_content_slot($content, 'teaser', 'MultiLineText');

$content = $q->fetchOne();

$slots = $content->getSlots();
$slots['title']['value'] = 'Title value';
$slots['body']['value'] = 'Body value';
$slots['teaser']['value'] = "Body value\nTesting";
$slots->save();
$content->save();

$countBefore = count($slots);
$slot = $content->getOrCreateSlot('title');
$t->is($slot->name, 'title');
$slot = $content->getOrCreateSlot('new');
$t->is($slot->name, 'new');
$t->is($slot->Type->name, 'Text');

$content = $q->fetchOne();

$t->is(count($content->getSlots()), $countBefore + 1);

$t->is($content->title, 'Title value');
$t->is($slots['title']['value'], 'Title value');
$t->is($slots['title']['Type']['name'], 'ContentProperty');
$t->is($slots['body']['Type']['name'], 'Markdown');
$t->is($slots['teaser']['Type']['name'], 'MultiLineText');

$t->is($slots['title']->render(), 'Title value');
$t->is($slots['body']->render(), '<div class="sympal_markdown"><p>Body value</p>
</div>');

$slots['body']['value'] = "test";
$t->is($slots['body']->render(), '<div class="sympal_markdown"><p>test</p>
</div>');

$slots->save();

$slots[2]->Type = Doctrine_Core::getTable('sfSympalContentSlotType')->findOneByName('MultiLineText');
$t->is($slots['teaser']->render(), 'Body value<br />
Testing');

$content = sfSympalContent::createNew('sfSympalPage');

$t->is($content->Type->name, 'sfSympalPage');
$t->is(get_class($content->getRecord()), 'sfSympalPage');

$content->title = 'test';
$t->is($content->getRecord()->title, 'test');