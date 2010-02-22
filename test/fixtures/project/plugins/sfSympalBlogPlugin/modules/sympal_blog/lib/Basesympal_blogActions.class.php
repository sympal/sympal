<?php

/**
 * Base actions for the sfSympalBlogPlugin sympal_blog module.
 * 
 * @package     sfSympalBlogPlugin
 * @subpackage  sympal_blog
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_blogActions extends sfActions
{
  /**
   * An action that filters posts by year and month
   */
  public function executeMonth(sfWebRequest $request)
  {
    $month = $request->getParameter('m');
    $year = $request->getParameter('y');
    
    $this->menuItem = $this->getBlogMenuItem();
    $this->pager = Doctrine::getTable('sfSympalBlogPost')->retrieveBlogMonth($month, $year);
    $this->content = $this->pager->getResults();

    $this->breadcrumbsTitle = date('M Y', strtotime($month.'/01/'.$year));
    $this->title = 'Posts for the month of ' . $this->breadcrumbsTitle;
    
    $this->setTemplate('list');
  }
  
  /**
   * An action that filters posts by tags. This requires the sfSympalTagsPlugin
   */
  public function executeTag(sfWebRequest $request)
  {
    if (!in_array('sfSympalBlogPlugin', $this->getSympalContext()->getSympalConfiguration()->getInstalledPlugins()))
    {
      throw new sfException('sympal_blog/tag action requires sfSympalBlogPlugin to be installed');
    }
    
    $tag = $request->getParameter('tag');
    
    // setup the page
    $q = Doctrine::getTable('sfSympalTag')->getContentQueryByTag('sfSympalBlogPost', $tag);
    $q->orderBy('c.date_published DESC');
    
    $this->pager = new sfDoctrinePager('sfSympalContent', sfSympalConfig::get('rows_per_page'));
    $this->pager->setQuery($q);
    $this->pager->init();
    
    $this->menuItem = $this->getBlogMenuItem();
    $this->content = $this->pager->getResults();
  
    $this->breadcrumbsTitle = $tag;
    $this->title = sprintf('Posts tagged with "%s"', $tag);
    
    $this->setTemplate('list');
  }
  
  /**
   * Returns the default blog menu item so that the breadcrumbs can be
   * properly rendered
   * 
   * @return sfSympalMenuItem
   */
  protected function getBlogMenuItem()
  {
    return Doctrine::getTable('sfSympalMenuItem')->findOneBySlug('blog');
  }
}
