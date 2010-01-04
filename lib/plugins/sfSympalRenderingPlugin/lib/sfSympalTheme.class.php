<?php

class sfSympalTheme
{
  protected
    $_layoutPath,
    $_cssPath;

  public function __construct($name)
  {
    $this->_layoutPath = $this->_findLayoutPath($name);
    $this->_cssPath = sfSympalConfig::getAssetPath($this->_findCssPath($name));

    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $response = $context->getResponse();

    $actionEntry = $context->getController()->getActionStack()->getLastEntry();
    $module = $actionEntry ? $actionEntry->getModuleName():$request->getParameter('module');
    $action = $actionEntry ? $actionEntry->getActionName():$request->getParameter('action');

    $info = pathinfo($this->_layoutPath);
    $path = $info['dirname'].'/'.$info['filename'];
    $name = $info['filename'];

    sfConfig::set('symfony.view.'.$module.'_'.$action.'_layout', $path);
    sfConfig::set('symfony.view.sympal_default_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_default_secure_layout', $path);

    if ($lastStylesheet = sfSympalConfig::get('last_stylesheet'))
    {
      $response->removeStylesheet($lastStylesheet);
    }

    $response->addStylesheet($this->_cssPath, 'last');

    sfSympalConfig::set('last_stylesheet', $this->_cssPath);
  }

  public static function change($name)
  {
    if (!$name)
    {
      return false;
    }

    $theme = new self($name);

    return true;
  }

  public static function loadDefault()
  {
    return self::change(sfSympalConfig::get('default_layout'));
  }

  public function getLayoutPath()
  {
    return $this->_layoutPath;
  }

  public function getCssPath()
  {
    return $this->_cssPath;
  }

  protected function _findLayoutPath($name)
  {
    $sympalConfiguration = sfSympalContext::getInstance()->getSympalConfiguration();

    $layouts = $sympalConfiguration->getLayouts();
    $path = array_search($name, $layouts);

    if (!sfToolkit::isPathAbsolute($path))
    {
      $path = sfConfig::get('sf_root_dir').'/'.$path;
    }

    return $path;
  }

  /**
   * TODO: Optimize this eventually with some kind of cache so we know the path to all
   * theme css and js files and don't have to find them each time
   */
  protected function _findCssPath($name)
  {
    if (strpos($this->_layoutPath, 'sfSympalPlugin/templates') !== false)
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