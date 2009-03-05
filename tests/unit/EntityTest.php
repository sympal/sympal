<?php
$app = 'frontend';
$database = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(34, new lime_output_color());

$page = new Page();
$page->name = 'Testing this out';
$page->title = 'Body of the page...';
$page->save();

$menuItem = new MenuItem();
$menuItem->name = 'test';
$menuItem->RelatedEntity = $page->Entity;
$menuItem->Site->setTitle(sfSympalTools::getCurrentSite());
$menuItem->save();

$page->Entity->MasterMenuItem = $menuItem;
$page->save();

$entities = Doctrine::getTable('Entity')
  ->createQuery('e')
  ->leftJoin('e.Site s')
  ->leftJoin('e.Type t')
  ->leftJoin('e.Page p')
  ->leftJoin('p.Translation tr')
  ->andWhere('e.slug = ?', 'testing-this-out')
  ->fetchArray();

$t->is(isset($entities[0]['Type']), true);
$t->is($entities[0]['Type']['name'], 'Page');
$t->is($entities[0]['Type']['label'], 'Page');
$t->is(isset($entities[0]['Site']), true);
$t->is($entities[0]['Site']['title'], 'Sympal');
$t->is($entities[0]['Site']['slug'], 'sympal');
$t->is(isset($entities[0]['Page']), true);
$t->is($entities[0]['slug'], 'testing-this-out');

$user = new sfGuardUser();
$user->username = 'test';
$user->password = 'test';
$user->save();

$entity = Doctrine::getTable('Entity')
  ->getTypeQuery('Page')
  ->andWhere('e.slug = ?', 'testing-this-out')
  ->fetchOne();

$t->is($entity->userHasLock($user), false);

$entity->obtainLock($user);
$t->is($entity->userHasLock($user), true);

$entity->releaseLock();
$t->is($entity->userHasLock($user), false);

$entity->publish();
$t->is($entity->is_published, true);
$t->is(strtotime($entity->date_published) > 0, true);

$entity->unpublish();
$t->is($entity->is_published, false);
$t->is(strtotime($entity->date_published) > 0, false);

$entity->is_published = 1;
$entity->save();
$entity->refresh();
$t->is($entity->is_published, true);
$t->is(strtotime($entity->date_published) > 0, true);

$menuItem = $entity->getMainMenuItem();

$t->is($menuItem->name, 'test');

$page = $entity->getRecord();
$t->is($page instanceof Page, true);
$t->is($page->name, 'Testing this out');

$template = $entity->getTemplate();
$t->is($template instanceof EntityTemplate, true);

$t->is($entity->getTitle(), 'Testing this out');
$t->is($entity->getHeaderTitle(), 'Testing this out');

$t->is($entity->getLayout(), 'sympal');
$t->is($entity->getRoute(), '@sympal_entity_view_type_1?slug=testing-this-out');

$configuration->loadHelpers('Entity');

$t->is(entity_slot($entity, 'title', 'Text', 'Test'), 'Test');
$t->is(entity_slot($entity, 'body', 'Markdown'), '[Double click to edit slot content]');
$t->is(entity_slot($entity, 'teaser', 'MultiLineText'), '[Double click to edit slot content]');
$entity->refresh(true);

$slots = $entity->getSlots();
$slots[0]['value'] = 'Title value';
$slots[1]['value'] = 'Body value';
$slots[2]['value'] = "Body value\nTesting";
$slots->save();

$t->is($slots[0]->render(), 'Title value');
$t->is($slots[1]->render(), '<div class="sympal_markdown"><p>Body value</p>
</div>');

$t->is(render_entity_slot($slots[0]), 'Title value');

// test php
$slots[1]['value'] = "<?php echo 'test'; ?>";
$t->is(render_entity_slot($slots[1]), '<div class="sympal_markdown"><p>test</p>
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
$t->is(render_entity_slot($slots[1]), $html);

$slots->save();

$t->is(render_entity_slot($slots[2]), 'Body value<br />
Testing');