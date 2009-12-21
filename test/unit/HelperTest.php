<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(4, new lime_output_color());

$browser = new sfTestFunctional(new sfBrowser());
$browser->get('/');

$menuItem = Doctrine_Core::getTable('sfSympalMenuItem')->findOneBySlug('sample-page');
$t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home / Sample Page');
$t->is(get_sympal_breadcrumbs($menuItem), '<div id="sympal_breadcrumbs"><ul id="breadcrumbs-menu"><li id="breadcrumbs-home" class="first"><a href="/index.php/">Home</a></li><li id="breadcrumbs-sample-page" class="last">Sample Page</li></ul></div>');

$breadcrumbs = array(
  'Home' => '@homepage',
  'About' => 'http://www.google.com',
  'Jonathan H. Wage' => 'http://www.jwage.com'
);
$t->is(get_sympal_breadcrumbs($breadcrumbs), '<div id="sympal_breadcrumbs"><ul id="breadcrumbs-menu"><li id="breadcrumbs-home" class="first"><a href="/index.php/">Home</a></li><li id="breadcrumbs-about"><a href="http://www.google.com">About</a></li><li id="breadcrumbs-jonathan-h-wage" class="last">Jonathan H. Wage</li></ul></div>');

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



<pre><code class="yaml"><span class="yaml_top_dashes">---</span>
<span class="yaml_keys">User</span><span class="yaml_colon">:</span>
<span class="yaml_keys">  columns</span><span class="yaml_colon">:</span>
<span class="yaml_keys">    username</span><span class="yaml_colon">:</span><span class="yaml_string"> string(255)</span>
</code></pre>
</div>';

$t->is(sfSympalMarkdownRenderer::convertToHtml($markdown), $html);