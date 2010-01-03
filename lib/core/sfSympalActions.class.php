<?php

class sfSympalActions extends sfSympalExtendClass
{
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

  public function loadSympalContentRenderer()
  {
    $request = $this->getRequest();
    $response = $this->getResponse();

    $content = null;
    $e = null;

    $content = $this->getRoute()->getObject();

    $this->_handleForward404($content, $e);
    $this->getUser()->checkContentSecurity($content);

    $this->changeTheme($content->getLayoutToRenderWith());

    $renderer = $this->getSympalContext()->getContentRenderer($content);

    $content->loadMetaData($response);

    if ($renderer->getFormat() != 'html')
    {
      sfConfig::set('sf_web_debug', false);

      $format = $request->getRequestFormat();
      $request->setRequestFormat('html');
      $this->setLayout(false);

      if ($mimeType = $request->getMimeType($format))
      {
        $response->setContentType($mimeType);
      }
    }

    return $renderer;
  }

  private function _handleForward404($record, Exception $e = null)
  {
    if (!$record)
    {
      $sympalContext = $this->getSympalContext();
      $site = $sympalContext->getSite();
      if (!$site)
      {
        $message = sprintf(
          'The Symfony application "%s" does not have a site record in the database. You must either run the sympal:create-site %s or the sympal:install %s task in order to get started.',
          sfConfig::get('sf_app'),
          sfConfig::get('sf_app'),
          sfConfig::get('sf_app')
        );
        throw new sfException($message);
      } else {
        $q = Doctrine_Query::create()
          ->from('sfSympalContent c')
          ->andWhere('c.site_id = ?', $site->getId());
        $count = $q->count();
        if (!$count)
        {
          $this->forward('sympal_default', 'new_site');
        }

        if ($e)
        {
          throw $e;
        } else {
          $this->forward404();
        }
      }
    }
  }

  public function quickRenderContent($type, $slug, $format = 'html')
  {
    $content = Doctrine_Core::getTable('sfSympalContent')
      ->getFullTypeQuery($type)
      ->andWhere('c.slug = ?', $slug)
      ->fetchOne();

    $menuItem = $content->getMenuItem();

    $renderer = new sfSympalContentRenderer($this, $menuItem, $format);
    $renderer->setContent($content);
    $renderer->initialize();

    return $renderer;
  }

  public function changeTheme($name)
  {
    return sfSympalTheme::change($name);
  }

  public function loadDefaultLayout()
  {
    return sfSympalTheme::loadDefault();
  }

  public function useAdminLayout()
  {
    $response = $this->getResponse();
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/js/jQuery.cookie.js'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.js'));
    $response->addJavascript(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/js/admin.js'));
    $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalPlugin/fancybox/jquery.fancybox.css'));
    $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/css/global.css'));
    $response->addStylesheet(sfSympalConfig::getAssetPath('/sfSympalAdminPlugin/css/default.css'));

    sfSympalToolkit::useJQuery();

    $this->changeTheme(sfSympalConfig::get('admin_layout', null, 'admin'));
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
}