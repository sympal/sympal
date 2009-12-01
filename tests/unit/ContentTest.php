<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(33, new lime_output_color());

$user = new User();
$user->first_name = 'test';
$user->last_name = 'test';
$user->email_address = 'test@gmail.com';
$user->username = rand();
$user->password = 'test';
$user->save();

$page = new Page();
$page->title = 'Testing this out';

$content = Content::createNew('Page');
$content->slug = 'testing-this-out';
$content->is_published = true;
$content->date_published = date('Y-m-d', time() - 3600);
$content->CreatedBy = $user;
$content->save();

$page->Content = $content;
$page->save();

$menuItem = new MenuItem();
$menuItem->name = 'test';
$menuItem->RelatedContent = $page->Content;
$menuItem->is_published = true;
$menuItem->save();

$page->Content->MasterMenuItem = $menuItem;
$page->save();

$content = Doctrine_Core::getTable('Content')
  ->createQuery('c')
  ->leftJoin('c.Site s')
  ->leftJoin('c.Type t')
  ->leftJoin('c.Page p')
  ->andWhere('c.slug = ?', 'testing-this-out')
  ->fetchArray();

$t->is(isset($content[0]['Type']), true);
$t->is($content[0]['Type']['name'], 'Page');
$t->is($content[0]['Type']['label'], 'Page');
$t->is(isset($content[0]['Site']), true);
$t->is($content[0]['Site']['title'], 'Sympal');
$t->is($content[0]['Site']['slug'], 'sympal');
$t->is(isset($content[0]['Page']), true);
$t->is($content[0]['slug'], 'testing-this-out');

class testMyUser extends myUser
{
  public function getSympalUser()
  {
    global $user;
    return $user;
  }
}

$sfUser = sfContext::getInstance()->getUser();
$sfUser->signIn($user);
$sfUser->isEditMode(true);

$q = Doctrine_Core::getTable('Content')
  ->getTypeQuery('Page')
  ->andWhere('c.slug = ?', 'testing-this-out');

$content = $q->fetchOne();

$t->is($content->userHasLock($sfUser), false);

$content->obtainLock($sfUser);
$t->is($content->userHasLock($sfUser), true);

$content->releaseLock();
$t->is($content->userHasLock($sfUser), false);

$content->publish();
$t->is($content->is_published, true);
$t->is(strtotime($content->date_published) > 0, true);

$content->unpublish();
$t->is($content->is_published, false);
$t->is(strtotime($content->date_published) > 0, false);

$content->is_published = 1;
$content->save();
$content->refresh();
$t->is($content->is_published, true);
$t->is(strtotime($content->date_published) > 0, true);

$menuItem = $content->getMainMenuItem();

$t->is($menuItem->name, 'test');

$page = $content->getRecord();
$t->is($page instanceof Page, true);
$t->is($page->title, 'Testing this out');

$template = $content->getTemplate();
$t->is($template instanceof ContentTemplate, true);

$t->is($content->getTitle(), 'Testing this out');
$t->is($content->getHeaderTitle(), 'Testing this out');

$t->is($content->getLayout(), 'sympal');
$t->is($content->getRoute(), '@sympal_content_view_type_page?slug=testing-this-out');

get_sympal_content_slot($content, 'title', 'Text');
get_sympal_content_slot($content, 'body', 'Markdown');
get_sympal_content_slot($content, 'teaser', 'MultiLineText');

$content->refresh(true);

$slots = $content->getSlots();
$slots[0]['value'] = 'Title value';
$slots[1]['value'] = 'Body value';
$slots[2]['value'] = "Body value\nTesting";
$slots->save();

$t->is($slots[0]->render(), 'Title value');
$t->is($slots[1]->render(), '<div class="sympal_markdown"><p>Body value</p>
</div>');

$t->is($slots[0]->render(), 'Title value');

// test php
$slots[1]['value'] = "<?php echo 'test'; ?>";
$t->is($slots[1]->render(), '<div class="sympal_markdown"><p>test</p>
</div>');

$slots->save();

$slots[2]->Type = Doctrine_Core::getTable('ContentSlotType')->findOneByName('MultiLineText');
$t->is($slots[2]->render(), 'Body value<br />
Testing');

$content = Content::createNew('Page');

$t->is($content->Type->name, 'Page');
$t->is(get_class($content->getRecord()), 'Page');

$content->title = 'test';
$t->is($content->getRecord()->title, 'test');