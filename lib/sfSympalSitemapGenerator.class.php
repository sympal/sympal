<?php

/**
 * Class responsible for generating a valid XML sitemap for all content
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalSitemapGenerator
{
  protected
    $_site,
    $_baseUrl;

  public function __construct($site)
  {
    $this->_site = $site;
  }

  /**
   * Generate the XML sitemap
   *
   * @return string $html
   */
  public function generate()
  {
    return sprintf('<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
%s
</urlset>',
      $this->_generateUrlsXml($this->_getContent())
    );
  }

  /**
   * Get all content to build the sitemap from
   *
   * @return Doctrine_Collection $collection
   */
  protected function _getContent()
  {
    return Doctrine_Core::getTable('sfSympalContent')
      ->createQuery('c')
      ->select('c.*, t.*')
      ->innerJoin('c.Type t')
      ->innerJoin('c.Site s WITH s.slug = ?', $this->_site)
      ->execute();
  }

  /**
   * Generate the URLs XML
   *
   * @param Doctrine_Collection $contentCollection 
   * @return string $xml
   */
  protected function _generateUrlsXml(Doctrine_Collection $contentCollection)
  {
    $urls = array();
    foreach($contentCollection as $content)
    {
      $urls[] = $this->_generateUrlXml($content);
    }

    return implode("\n", $urls);
  }

  /**
   * Generate the URL XML
   *
   * @param sfSympalContent $content 
   * @return string $xml
   */
  protected function _generateUrlXml(sfSympalContent $content)
  {
    return sprintf('  <url>
    <loc>
      %s
    </loc>
  </url>', $content->getUrl(array('absolute' => true)));
  }
}