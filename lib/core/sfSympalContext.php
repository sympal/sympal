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
    $_currentSite;

  public function __construct($siteSlug, sfSympalConfiguration $sympalConfiguration, sfContext $symfonyContext)
  {
    $this->_siteSlug = $siteSlug;
    $this->_sympalConfiguration = $sympalConfiguration;
    $this->_symfonyContext = $symfonyContext;
  }

  public function getCurrentMenuItem()
  {
    return $this->_currentMenuItem;
  }

  public function setCurrentMenuItem(sfSympalMenuItem $menuItem)
  {
    $this->_currentMenuItem = $menuItem;
  }

  public function getCurrentContent()
  {
    return $this->_currentContent;
  }

  public function setCurrentContent(sfSympalContent $content)
  {
    $this->_currentContent = $content;
  }

  public function getSite()
  {
    if (!$this->_site)
    {
      $this->_site =  Doctrine_Core::getTable('sfSympalSite')
        ->createQuery('s')
        ->where('s.slug = ?', $this->_siteSlug)
        ->fetchOne();
    }
    return $this->_site;
  }

  public function getSiteSlug()
  {
    return $this->_siteSlug;
  }

  public function getSympalConfiguration()
  {
    return $this->_sympalConfiguration;
  }

  public function getSymfonyContext()
  {
    return $this->_symfonyContext;
  }

  public function getContentRenderer(sfSympalContent $content, $format = null)
  {
    return new sfSympalContentRenderer($this, $content, $format);
  }

  public static function getInstance($site = null)
  {
    if (is_null($site))
    {
      return self::$_current;
    }

    if (!isset(self::$_instances[$site]))
    {
      throw new sfException($site.' instance does not exist.');
    }
    return self::$_instances[$site];
  }

  public static function createInstance($site, sfContext $symfonyContext)
  {
    $sympalConfiguration = $symfonyContext->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration();

    $instance = new self($site, $sympalConfiguration, $symfonyContext);
    self::$_instances[$site] = $instance;
    self::$_current = $instance;

    return self::$_instances[$site];
  }

  public function __call($method, $arguments)
  {
    return sfSympalExtendClass::extendEvent($this, $method, $arguments);
  }
}