<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../../../bootstrap/unit.php');

$t = new lime_test(0);

$site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug($app);

$page = sfSympalContent::createNew('sfSympalPage');
$page->date_published = date('Y-m-d h:i:s', time() + 86400); // tomorrow
$page->Site = $site;
$page->title = 'My test page';
$page->save();

$pageContentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName('sfSympalPage');

$contentList = sfSympalContent::createNew('sfSympalContentList');
$contentList->ContentType = $pageContentType;
$contentList->Site = $site;
$contentList->title = 'My test content list';
$contentList->save();

/**
 * This will test PluginsfSympalContentList, but buildDataGrid() currently
 * has an sfWebRequest dependency on it, which needs to be removed.
 * See ticket #23
 */