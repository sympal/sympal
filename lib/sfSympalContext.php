<?php

class sfSympalContext
{
  protected static
    $_instances = array(),
    $_current;

  protected
    $_site,
    $_siteSlug,
    $_sympalConfiguration,
    $_symfonyContext,
    $_currentMenuItem,
    $_currentContent,
    $_currentSite,
    $_previousTheme,
    $_theme,
    $_themeObjects = array();

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
   * Get the current theme
   *
   * @return string $theme
   */
  public function getTheme()
  {
    return $this->_theme;
  }

  /**
   * Get the theme object for the current theme or a given theme name
   *
   * @param string $name 
   * @return sfSympalTheme $theme
   */
  public function getThemeObject($name = null)
  {
    $theme = $name ? $name : $this->_theme;
    if (!isset($this->_themeObjects[$theme]))
    {
      $configurationArray = sfSympalConfig::get('themes', $theme);
      $configurationArray['name'] = $theme;

      $configurationClass = isset($configurationArray['config_class']) ? $configurationArray['class'] : 'sfSympalThemeConfiguration';
      $themeClass = isset($configurationArray['theme_class']) ? $configurationArray['theme_class'] : 'sfSympalTheme';
      $configuration = new $configurationClass($configurationArray);
      $this->_themeObjects[$theme] = new $themeClass($this, $configuration);
    }
    return isset($this->_themeObjects[$theme]) ? $this->_themeObjects[$theme] : false;
  }

  /**
   * Get array of all instantiated theme objects
   *
   * @return array $themeObjects
   */
  public function getThemeObjects()
  {
    return $this->_themeObjects;
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
   * Get the previous theme object that was loaded
   *
   * @return sfSympalTheme $theme
   */
  public function getPreviousTheme()
  {
    return $this->getThemeObject($this->_previousTheme);
  }

  /**
   * Unload the previous theme object that was loaded
   *
   * @return void
   */
  public function unloadPreviousTheme()
  {
    if ($previousTheme = $this->getPreviousTheme())
    {
      $previousTheme->unload();
    }
  }

  /**
   * Set the current theme name
   *
   * @param string $theme 
   * @return void
   */
  public function setTheme($theme)
  {
    $this->_theme = $theme;
  }

  /**
   * Load a given theme or the current configured theme in ->_theme
   *
   * @param string $name Optional theme name to load
   * @return void
   */
  public function loadTheme($name = null)
  {
    $this->_previousTheme = $this->_theme;
    $theme = $name ? $name : $this->_theme;
    $this->setTheme($theme);
    if ($theme = $this->getThemeObject($theme))
    {
      $theme->load();
    }
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