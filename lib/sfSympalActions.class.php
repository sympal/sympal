<?php

class sfSympalActions extends sfSympalExtendClass
{
  public function resetSympalRoutesCache()
  {
    // Reset the routes cache incase of the url changing or a custom url was added
    return $this->getContext()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')
      ->getSympalConfiguration()->getCache()->resetRouteCache();
  }

  public function clearCache(array $options = array())
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->getContext()->getEventDispatcher(), new sfFormatter());
    $task->run(array(), $options);

    $this->resetSympalRoutesCache();
  }

  public function clearMenuCache()
  {
    $files = glob(sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/SYMPAL_MENU_*.cache');
    foreach ((array) $files as $file)
    {
      unlink($file);
    }
  }

  public function isAjax()
  {
    $request = $this->getRequest();
    return $request->isXmlHttpRequest() || $request->getParameter('is_ajax');
  }

  public function getSympalContext()
  {
    return sfSympalContext::getInstance();
  }

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
      $this->getUser()->setFlash('error', 'Sympal requires that some files and folders are writeable in your project. The file "'.$check.'" specifically is not writeable. Run the sympal:fix-perms task to fix or manually adjust the permissions.');
    }

    if ($error)
    {
      return false;
    } else {
      return true;
    }
  }

  public function getSympalContentActionLoader()
  {
    return $this->getSympalContext()->getSympalContentActionLoader($this->getSubject());
  }

  public function loadTheme($name)
  {
    return $this->getSympalContext()->loadTheme($name);
  }

  public function loadThemeOrDefault($name)
  {
    if ($name)
    {
      return $this->getSympalContext()->loadTheme($name);
    } else {
      return $this->getSympalContext()->loadTheme(sfSympalConfig::get('default_theme'));
    }
  }

  public function loadDefaultTheme()
  {
    $this->loadTheme(sfSympalConfig::get('default_theme'));
  }

  public function loadAdminTheme()
  {
    $this->loadTheme(sfSympalConfig::get('admin_theme', null, 'admin'));
  }

  public function loadSiteTheme()
  {
    $this->loadThemeOrDefault($this->getSympalContext()->getSite()->getTheme());
  }

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

  public function goBack()
  {
    $this->redirect($this->getRequest()->getReferer());
  }

  public function refresh()
  {
    $this->redirect($this->getRequest()->getUri());
  }
}