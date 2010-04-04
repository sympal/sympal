<?php

/**
 * Class responsible for keeping track of the current sfSympalSite object
 * 
 * @package     sfSympalContentPlugin
 * @subpackage  service
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-30
 * @version     svn:$Id$ $Author$
 */
class sfSympalSiteManager
{
  protected
    $_dispatcher,
    $_configuration;
  
  protected
    $_siteSlug,
    $_site;
  
  protected
    $_currentContent,
    $_currentMenuItem;

  /**
   * Class Constructor
   * 
   * @param sfSympalConfiguration $configuration The sympal configuration
   */
  public function __construct(sfSympalConfiguration $configuration)
  {
    $this->_dispatcher = $configuration->getEventDispatcher();
    $this->_configuration = $configuration;
    $this->_siteSlug = $configuration->getProjectConfiguration()->getApplication();
  }

  /**
   * Get the current sfSympalSite instance
   *
   * @return sfSympalSite $site
   */
  public function getSite()
  {
    if (!$this->_site)
    {
      $this->_site =  Doctrine_Core::getTable('sfSympalSite')
        ->createQuery('s')
        ->where('s.slug = ?', $this->_siteSlug)
        ->enableSympalResultCache('sympal_context_get_site')
        ->fetchOne();
    }

    return $this->_site;
  }

  /**
   * Set the current sfSympalSite instance
   *
   * @param sfSympalSite $site 
   * @return void
   */
  public function setSite(sfSympalSite $site)
  {
    $this->_site = $site;
  }

  /**
   * Get the current site slug
   *
   * @return string $siteSlug
   */
  public function getSiteSlug()
  {
    return $this->_siteSlug;
  }

  /**
   * Get the current sfSympalContent instance for this sympal context
   *
   * @return sfSympalContent $content
   */
  public function getCurrentContent()
  {
    return $this->_currentContent;
  }

  /**
   * Set the current sfSympalContent instance for this sympal context
   *
   * @param sfSympalContent $content 
   * @return void
   */
  public function setCurrentContent(sfSympalContent $content)
  {
    $this->_currentContent = $content;
    if (!$this->_site)
    {
      $this->_site = $content->getSite();
    }

    $this->_dispatcher->notify(new sfEvent($this->_currentContent, 'sympal.content.set_content'));
  }
}