<?php

/**
 * Base components for the sfSympalBlogPlugin sympal_blog module.
 * 
 * @package     sfSympalBlogPlugin
 * @subpackage  sympal_blog
 * @author      Your name here
 * @version     SVN: $Id: BaseComponents.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_blogComponents extends sfComponents
{
  public function executeSidebar()
  {
    $config = sfSympalConfig::get('blog-post', 'sidebar', array());
    
    if (isset($config['display']))
    {
      $this->widgets = array();
      foreach($config['display'] as $name)
      {
        if (in_array($name, $config['display']))
        {
          $this->widgets[] = $config['widgets'][$name];
        }
      }
    }
    else
    {
      $this->widgets = $config['widgets'];
    }
  }
  
  /**
   * Executes the "latest posts" section of the sidebar
   */
  public function executeLatest_posts(sfWebRequest $request)
  {
    $this->latestPosts = Doctrine::getTable('sfSympalBlogPost')->retrieveLatestPosts(5);
  }
  
  /**
   * Executes the "top authors" section of the sidebar
   */
  public function executeTop_authors(sfWebRequest $request)
  {
    $this->authors = Doctrine::getTable('sfSympalBlogPost')->retrieveTopAuthors(5);
  }
  
  /**
   * Executes the "top authors" section of the sidebar
   */
  public function executeMonthly_history(sfWebRequest $request)
  {
    $this->months = Doctrine::getTable('sfSympalBlogPost')->retrieveMonths();
  }
}