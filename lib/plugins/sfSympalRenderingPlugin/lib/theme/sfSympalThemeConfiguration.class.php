<?php

class sfSympalThemeConfiguration
{
  protected $_configuration;

  public function __construct($configuration)
  {
    $this->_configuration = $configuration;
    $this->_layoutPath = $this->_findLayoutPath($this->getLayout());
  }

  public function toArray()
  {
    return $this->_configuration;
  }

  public function getName()
  {
    return $this->_configuration['name'];
  }

  public function getLayout()
  {
    return isset($this->_configuration['layout']) ? $this->_configuration['layout'] : $this->getName();
  }

  public function getLayoutPath()
  {
    return $this->_layoutPath;
  }

  public function getStylesheets()
  {
    return isset($this->_configuration['stylesheets']) ? $this->_configuration['stylesheets'] : array($this->_findStylesheetPath());
  }

  public function getJavascripts()
  {
    return isset($this->_configuration['javascripts']) ? $this->_configuration['javascripts'] : array();    
  }

  public function getCallables()
  {
    return isset($this->_configuration['callables']) ? $this->_configuration['callables'] : array();
  }

  /**
   * Calculates the location of the layout, which could live in several locations.
   * 
   * Specifically, the layout file for a theme could live in any "templates"
   * file found in the application dir or any enabled plugins
   * 
   * @param string $layout The optional layout to look for (defaults to this theme's name)
   */
  protected function _findLayoutPath($layout = null)
  {
    if ($layout === null)
    {
      $layout = $this->getName();
    }
    $sympalConfiguration = sfSympalContext::getInstance()->getSympalConfiguration();

    $layouts = $sympalConfiguration->getLayouts();
    $path = array_search($layout, $layouts);

    if (!$path)
    {
      throw new InvalidArgumentException(sprintf('Could not find layout "%s" in any "templates" directories. You may need to clear your cache.', $layout));
    }

    if (!sfToolkit::isPathAbsolute($path))
    {
      $path = sfConfig::get('sf_root_dir').'/'.$path;
    }

    return $path;
  }

  protected function _findStylesheetPath()
  {
    $name = $this->getName();
    if (strpos($this->getLayoutPath(), 'sfSympalPlugin/templates') !== false)
    {
      return '/sfSympalPlugin/css/' . $name . '.css';
    } else {
      if (is_readable(sfConfig::get('sf_web_dir').'/css/'.$name.'.css'))
      {
        return $name;
      } else {
        $configuration = sfContext::getInstance()->getConfiguration();
        $pluginPaths = $configuration->getAllPluginPaths();

        foreach ($pluginPaths as $plugin => $path)
        {
          if (file_exists($path.'/web/css/'.$name.'.css'))
          {
            return '/'.$plugin.'/css/'.$name.'.css';
          }
        }
      }
    }
  }
}