<?php

class sfSympalSitemapGenerator
{
  protected
    $_site,
    $_baseUrl;

  public function __construct($site)
  {
    $this->_site = $site;
  }

  public function generate()
  {
    return sprintf('<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
%s
</urlset>',
    $this->_generateUrlsXml($this->_getContent())
    );
  }

  protected function _getContent()
  {
    return Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('c.*, t.*')
      ->innerJoin('c.Type t')
      ->innerJoin('c.Site s WITH s.slug = ?', $this->_site)
      ->execute();
  }

  protected function _generateUrlsXml(Doctrine_Collection $contentCollection)
  {
    $urls = array();
    foreach($contentCollection as $content)
    {
      $urls[] = $this->_generateUrlXml($content);
    }

    return implode("\n", $urls);
  }

  protected function _generateUrlXml(sfSympalContent $content)
  {
    return sprintf('  <url>
    <loc>
      %s
    </loc>
  </url>', $content->getUrl(array('absolute' => true)));
  }
}