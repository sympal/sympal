<?php

/**
 * Context class for a Sympal instance
 * 
 * A Sympal "context" is a singleton with respect to an individual sfSympalSite
 * record. This is very similar to sfContext, which is a singleton with respect
 * to each symfony app.
 * 
 * If some object has a dependency on a symfony app but NOT an sfSympalSite
 * record, then it should be handled by sfContext. If it DOES have a
 * dependency on the current sfSympalSite record, it'll be handled here
 * on the sfSympalContext instance.
 * 
 * This manages things such as
 *   * The current sfSympalSite object
 *   * The current menu item
 *   * The current content object (sfSympalContent)
 * 
 * @package     sfSympalPlugin
 * @subpackage  util
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;

  protected
    $_site,
    $_siteSlug;
  
  protected
    $_sympalConfiguration,
    $_symfonyContext;
  
  protected
    $_currentMenuItem,
    $_currentContent;

  public function __construct(sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_siteSlug = $symfonyContext->getConfiguration()->getApplication();
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
  }

  /**
   * Get the current sfSympalMenuItem instance for this sympal context
   *
   * @return sfSympalMenuItem
   */
  public function getCurrentMenuItem()
  {
    if (!$this->_currentMenuItem)
    {
      $this->_currentMenuItem = sfSympalMenuSiteManager::getInstance()->findCurrentMenuItem();
    }

    return $this->_currentMenuItem;
  }

  /**
   * Set the current sfSympalMenuItem instance for this sympal context
   *
   * @param sfSympalMenuItem $menuItem
   * @return void
   */
  public function setCurrentMenuItem(sfSympalMenuItem $menuItem)
  {
    $this->_currentMenuItem = $menuItem;
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
    if ($menuItem = $content->getMenuItem())
    {
      $this->_currentMenuItem = $menuItem;
    }
  }

  /**
   * Set the current sfSympalSite instance for this sympal context
   *
   * @param sfSympalSite $site 
   * @return void
   */
  public function setSite(sfSympalSite $site)
  {
    $this->_site = $site;
  }

  /**
   * Get the current sfSympalSite instance for this sympal context
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
   * Shortcut to check if we should load the frontend editor
   *
   * @return boolean
   */
  public function shouldLoadFrontendEditor()
  {
    return $this->_symfonyContext->getConfiguration()->getPluginConfiguration('sfSympalEditorPlugin')->shouldLoadEditor();
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
   * Shortcut to check if we are inside an admin module
   *
   * @return boolean
   */
  public function isAdminModule()
  {
    return $this->_sympalConfiguration->isAdminModule();
  }

  /**
   * Get the current sfSympalConfiguration instance
   *
   * @return sfSympalConfiguration $sympalConfiguration
   */
  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  /**
   * Get the current sfContext instance
   *
   * @return sfContext $symfonyContext
   */
  public function getSymfonyContext()
  {
    return $this->_symfonyContext;
  }

  /**
   * Get a sfSympalContentRenderer instance for a given sfSympalContent instance
   *
   * @param sfSympalContent $content The sfSympalContent instance
   * @param string $format Optional format to render
   * @return sfSympalContentRenderer $renderer
   */
  public function getContentRenderer(sfSympalContent $content, $format = null)
  {
    return new sfSympalContentRenderer($this, $content, $format);
  }

  /**
   * Get a sfSympalContentActionLoader instance for a given sfActions instance
   *
   * @param sfActions $actions 
   * @return sfSympalContentActionLoader $loader
   */
  public function getSympalContentActionLoader(sfActions $actions)
  {
    return new sfSympalContentActionLoader($actions);
  }

  /**
   * Get a sfSympalContext instance
   *
   * @param string $site Optional site/app name to get
   * @return sfSympalContext $sympalContext
   */
  public static function getInstance($site = null)
  {
    if (is_null($site))
    {
      if (!self::$_current)
      {
        throw new InvalidArgumentException('Could not find a current sympal context instance');
      }
      return self::$_current;
    }

    if (!isset(self::$_instances[$site]))
    {
      throw new sfException($site.' instance does not exist.');
    }
    return self::$_instances[$site];
  }

  /**
   * Check if we have a sfSympalContext yet
   *
   * @param string $site Optional site/app name to check for
   * @return boolean
   */
  public static function hasInstance($site = null)
  {
    return is_null($site) ? !empty(self::$_instances) : isset(self::$_instances[$site]);
  }

  /**
   * Create a new sfSympalContext instance for a given sfContext and sfSympalConfiguration instance
   *
   * @param sfContext $symfonyContext 
   * @param sfSympalConfiguration $sympalConfiguration 
   * @return sfSympalContext $sympalContext
   */
  public static function createInstance(sfContext $symfonyContext, sfSympalConfiguration $sympalConfiguration)
  {
    $site = $symfonyContext->getConfiguration()->getApplication();

    $instance = new self($sympalConfiguration, $symfonyContext);
    self::$_instances[$site] = $instance;
    self::$_current = $instance;

    return self::$_instances[$site];
  }
}