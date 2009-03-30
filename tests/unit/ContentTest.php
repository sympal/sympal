<?php
$app = 'sympal';
$database = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(31, new lime_output_color());

$page = new Page();
$page->title = 'Testing this out';

$content = new Content();
$content->content_type_id = Doctrine::getTable('ContentType')->findOneByName('Page')->id;
$content->slug = 'testing-this-out';
$content->site_id = Doctrine::getTable('Site')->findOneByTitle('Sympal')->id;
$content->is_published = true;
$content->save();

$page->Content = $content;
$page->save();

$menuItem = new MenuItem();
$menuItem->name = 'test';
$menuItem->RelatedContent = $page->Content;
$menuItem->Site->setTitle('Sympal');
$menuItem->is_published = true;
$menuItem->save();

$page->Content->MasterMenuItem = $menuItem;
$page->save();

$content = Doctrine::getTable('Content')
  ->createQuery('e')
  ->leftJoin('e.Site s')
  ->leftJoin('e.Type t')
  ->leftJoin('e.Page p')
  ->andWhere('e.slug = ?', 'testing-this-out')
  ->fetchArray();

$t->is(isset($content[0]['Type']), true);
$t->is($content[0]['Type']['name'], 'Page');
$t->is($content[0]['Type']['label'], 'Page');
$t->is(isset($content[0]['Site']), true);
$t->is($content[0]['Site']['title'], 'Sympal');
$t->is($content[0]['Site']['slug'], 'sympal');
$t->is(isset($content[0]['Page']), true);
$t->is($content[0]['slug'], 'testing-this-out');

$user = new User();
$user->first_name = 'test';
$user->last_name = 'test';
$user->email_address = 'test@gmail.com';
$user->username = 'test';
$user->password = 'test';
$user->save();

class testMyUser extends myUser
{
  public function getGuardUser()
  {
    global $user;
    return $user;
  }
}

$sfUser = sfContext::getInstance()->getUser();
$sfUser->signIn($user);

$q = Doctrine::getTable('Content')
  ->getTypeQuery('Page')
  ->andWhere('e.slug = ?', 'testing-this-out');

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

$markdown = "
>**TIP**
>Testing tip

-

>**NOTE**
>Testing note

-

>**QUOTE**
>Testing quote

    [php]
    echo 'test';

-

    [yml]
    ---
    User:
      columns:
        username: string(255)
";

$html = '<div class="sympal_markdown"><blockquote class="tip"><p>
  Testing tip</p>
</blockquote>



<blockquote class="note"><p>
  Testing note</p>
</blockquote>



<blockquote class="quote"><p>
  Testing quote</p>
</blockquote>

<pre class="php"><span class="kw2">&lt;?php</span>
&nbsp;
<span class="kw3">echo</span> <span class="st0">\'test\'</span>;
&nbsp;
<span class="kw2">?&gt;</span></pre>



<pre><code class="yaml"><span style="color: #CC8865;">---</span>
<span style="color: #ffffdd;">User</span><span style="color: #5598EE;">:</span>
<span style="color: #ffffdd;">  columns</span><span style="color: #5598EE;">:</span>
<span style="color: #ffffdd;">    username</span><span style="color: #5598EE;">:</span><span style="color: #9EE665;"> string(255)</span>
</code></pre>
</div>';

$slots[1]['value'] = $markdown;
$t->is($slots[1]->render(), $html);

$slots->save();

$t->is($slots[2]->render(), 'Body value<br />
Testing');