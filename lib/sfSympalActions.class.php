<?php

/**
 * Class responsible for adding new methods to your sfActions instances
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalActions extends sfSympalExtendClass
{
  /**
   * Shortcut to reset the sympal routes cache from your actions
   *
   * @return void
   */
  public function resetSympalRoutesCache()
  {
    // Reset the routes cache incase of the url changing or a custom url was added
    return $this->getContext()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')
      ->getSympalConfiguration()->getCache()->resetRouteCache();
  }

  /**
   * Shortcut to the clear cache task from your actions
   *
   * @param array $options 
   * @return void
   */
  public function clearCache(array $options = array())
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->getContext()->getEventDispatcher(), new sfFormatter());
    $task->run(array(), $options);

    $this->resetSympalRoutesCache();
  }

  /**
   * Clear the menu cache from your actions
   *
   * @return void
   */
  public function clearMenuCache()
  {
    $files = glob(sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/*/SYMPAL_MENU_*.cache');
    foreach ((array) $files as $file)
    {
      unlink($file);
    }
  }

  /**
   * Check if this request is an ajax request
   *
   * @return boolean
   */
  public function isAjax()
  {
    $request = $this->getRequest();
    return $request->isXmlHttpRequest() || $request->getParameter('is_ajax');
  }

  /**
   * Get the current sfSympalContext instance from your actions
   *
   * @return sfSympalContext $sympalContext
   */
  public function getSympalContext()
  {
    return sfSympalContext::getInstance();
  }

  /**
   * Check that the file permissions are ok for the Sympal project from your actions
   *
   * @param array $items Array of files and/or directories to check
   * @return boolean
   */
  public function checkFilePermissions($items = null)
  {
    if ($items === null)
    {
      $items = array();

      if (file_exists(sfConfig::get('sf_upload_dir')))
      {
        $items[] = sfConfig::get('sf_upload_dir');
      }
      $items[] = sfConfig::get('sf_cache_dir');
      $items[] = sfConfig::get('sf_web_dir').'/cache';
      $items[] = sfConfig::get('sf_config_dir');
      $items[] = sfConfig::get('sf_data_dir').'/sql';
      $items[] = sfConfig::get('sf_log_dir');
      $items[] = sfConfig::get('sf_lib_dir');
      $items[] = sfConfig::get('sf_plugins_dir');
      $items[] = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony';
      $apps = glob(sfConfig::get('sf_apps_dir').'/*/config/app.yml');
      foreach ($apps as $app)
      {
        $items[] = $app;
      }
    } else {
      $items = (array) $items;
    }

    $dirs = sfFinder::type('dir')->in($items);
    $files = sfFinder::type('file')->in($items);
    $checks = array_merge($dirs, $files);

    $error = false;
    foreach ($checks as $check)
    {
      if (!is_writable($check))
      {
        $error = true;
        break;
      }
    }

    if ($error)
    {
      $this->getUser()->setFlash('error', sprintf(__('Sympal requires that some files and folders are writeable in your project. The file "%s" specifically is not writeable. Run the sympal:fix-perms task to fix or manually adjust the permissions.'), $check));
    }

    if ($error)
    {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Get instance of the sfSympalContentActionLoader for loading and rendering content
   *
   * @return sfSympalContentActionLoader
   */
  public function getSympalContentActionLoader()
  {
    return $this->getSympalContext()->getSympalContentActionLoader($this->getSubject());
  }

  /**
   * Load the given Sympal theme
   *
   * @param string $name 
   * @return void
   */
  public function loadTheme($name)
  {
    $this->getSympalContext()->loadTheme($name);
  }

  /**
   * Load a theme and if none given load the default theme
   *
   * @param string $name
   * @return void
   */
  public function loadThemeOrDefault($name)
  {
    if ($name)
    {
      $this->getSympalContext()->loadTheme($name);
    } else {
      $this->getSympalContext()->loadTheme(sfSympalConfig::get('default_theme'));
    }
  }

  /**
   * Load the default theme from your actions
   *
   * @return void
   */
  public function loadDefaultTheme()
  {
    $this->loadTheme(sfSympalConfig::get('default_theme'));
  }

  /**
   * Load the admin theme from your actions
   *
   * @return void
   */
  public function loadAdminTheme()
  {
    $this->loadTheme(sfSympalConfig::get('admin_theme', null, 'admin'));
  }

  /**
   * Load the theme for the current site
   *
   * @return void
   */
  public function loadSiteTheme()
  {
    $this->loadThemeOrDefault($this->getSympalContext()->getSite()->getTheme());
  }

  /**
   * Ask a for confirmation step from your actions
   *
   * @param string $title 
   * @param string $message 
   * @param array $variables
   * @return void
   */
  public function askConfirmation($title, $message, $variables = array())
  {
    $e = explode('/', $message);
    if (count($e) == 2)
    {
      try {
        $message = sfSympalToolkit::getSymfonyResource($e[0], $e[1], $variables);
      } catch (Exception $e) {
        throw new sfException('Invalid confirmation message: '.$e->getMessage());
      }
    }

    $request = $this->getRequest();

    if ($request->hasParameter('sympal_ask_confirmation') && $request->getParameter('sympal_ask_confirmation'))
    {
      if ($request->getParameter('yes'))
      {
        return true;
      } else {
        if ($this->isAjax())
        {
          $url = $request->getParameter('redirect_url');
          $this->redirect($url.(strpos($url, '?') !== false ? '&' : '?').'ajax=1');          
        } else {
          $this->redirect($request->getParameter('redirect_url'));
        }
      }
    } else {
      $this->getResponse()->setTitle($title);
      $request->setAttribute('title', $title);
      $request->setAttribute('message', $message);
      $request->setAttribute('is_ajax', $this->isAjax());

      $this->forward('sympal_default', 'ask_confirmation');
    }
  }

  /**
   * Forward to a given route with the array of parameters to be put in the request
   *
   * @param string $route
   * @param array $params
   * @return void
   */
  public function forwardToRoute($route, $params = array())
  {
    $full = $route;
    if (strstr($route, '?'))
    {
      $pos = strpos($route, '?');
      $route = substr($route, 1, $pos - 1);
    } else {
      $route = substr($route, 1, strlen($route));
    }

    $r = $this->getContext()->getRouting();
    $routes = $r->getRoutes();
    if ( ! isset($routes[$route]))
    {
      throw new sfException('Could not find route named: "' . $route . '"');
    }

    if (isset($pos))
    {
      $p = substr($full, $pos + 1, strlen($full));
      $e = explode('&', $p);

      foreach ($e as $k => $v)
      {
        $e2 = explode('=', $v);
        if ((isset($e2[0]) && $e2[0]) && (isset($e2[1]) && $e2[1]))
        {
          $params[$e2[0]] = $e2[1];
        }
      }
    }

    $routeInfo = $routes[$route];
    $params = array_merge($routeInfo->getDefaults(), $params);

    foreach ($params as $key => $value)
    {
      $this->getRequest()->setParameter($key, $value);
    }

    $this->forward($params['module'], $params['action']);
  }

  /**
   * Go back to the referrer.
   *
   * @return void
   */
  public function goBack()
  {
    $this->redirect($this->getRequest()->getReferer());
  }

  /**
   * Refresh the current uri
   *
   * @return void
   */
  public function refresh()
  {
    $this->redirect($this->getRequest()->getUri());
  }
}
