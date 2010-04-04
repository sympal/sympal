<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../../../bootstrap/unit.php');

$t = new lime_test(3);

class SitemapTest extends sfSympalSitemapGenerator
{
  protected function _getContent()
  {
    return Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('c.*, t.*')
      ->innerJoin('c.Type t')
      ->where('c.slug = ?', 'sample-page')
      ->execute();
  }
}

$test = new SitemapTest($app);
$xml = simplexml_load_string($test->generate());

$t->is(get_class($xml), 'SimpleXMLElement', '->generate() returns valid XML');
$loc = trim((string) $xml->url->loc);
$t->is(strpos($loc, 'http://') !== false, true, 'The first child\'s loc begins with http://');
$t->is(strpos($loc, 'sample-page') !== false, true, 'The first child\'s loc contains "sample-page"');