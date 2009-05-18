<?php
class sfSympalToolkit
{
  protected static
    $_currentMenuItem,
    $_currentContent,
    $_currentSite;

  public static function getCurrentMenuItem()
  {
    return self::$_currentMenuItem;
  }

  public static function setCurrentMenuItem(MenuItem $menuItem)
  {
    self::$_currentMenuItem = $menuItem;
  }

  public static function getCurrentContent()
  {
    return self::$_currentContent;
  }

  public static function setCurrentContent(Content $content)
  {
    self::$_currentContent = $content;
  }

  public static function getCurrentSite()
  {
    if (!self::$_currentSite)
    {
      self::$_currentSite =  Doctrine::getTable('Site')
        ->createQuery('s')
        ->where('s.slug = ?', sfConfig::get('sf_app'))
        ->fetchOne();
    }
    return self::$_currentSite;
  }

  public static function getCurrentSiteId()
  {
    return self::getCurrentSite()->id;
  }

  public static function getSymfonyResource($module, $action = null, $variables = array())
  {
    if (strpos($module, '/'))
    {
      $variables = (array) $action;
      $e = explode('/', $module);
      list($module, $action) = $e;
    }

    sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

    try {
      return get_component($module, $action, $variables);
    } catch (Exception $e1) {
      try {
        return get_partial($module.'/'.$action, $variables);
      } catch (Exception $e2) {
        try {
          return sfContext::getInstance()->getController()->getPresentationFor($module, $action);
        } catch (Exception $e3) {}
      }
    }

    throw new sfException('Could not find symfony resource for the module "'.$module.'" and action "'.$action.'". '.$e1->getMessage().' - '.$e2->getMessage().' - '.$e3->getMessage());
  }

  public static function getFirstApplication()
  {
    $apps = glob(sfConfig::get('sf_root_dir').'/apps/*');
    if (empty($apps))
    {
      return 'sympal';
    }
    $app = current($apps);
    $info = pathinfo($app);
    return $info['filename'];
  }

  public static function checkRequirements()
  {
    $user = sfContext::getInstance()->getUser();
    $app = sfConfig::get('sf_app');
    if (!$user instanceof sfSympalUser)
    {
      throw new sfException('myUser class located in '.sfConfig::get('sf_root_dir').'/apps/'.$app.'/myUser.class.php must extend sfSympalUser');
    }

    $routingPath = sfConfig::get('sf_root_dir').'/apps/'.$app.'/config/routing.yml';
    $routes = sfYaml::load(file_get_contents($routingPath));
    if (isset($routes['homepage']) || isset($routes['default_index']))
    {
      throw new sfException('Your application routing file must not have a homepage, default, or default_index route defined.');
    }

    $databasesPath = sfConfig::get('sf_config_dir').'/databases.yml';
    if(stristr(file_get_contents($databasesPath), 'propel'))
    {
      throw new sfException('Your project databases.yml must be configured to use Doctrine and not Propel.');
    }

    $apps = glob(sfConfig::get('sf_root_dir').'/apps/*');
    if (empty($apps))
    {
      throw new sfException('You must have at least one application created in order to use Sympal.');
    }
  }

  public static function processTemplate($code, $variables = array())
  {
    $sf_context = sfContext::getInstance();
    $vars = array(
      'sf_request' => $sf_context->getRequest(),
      'sf_response' => $sf_context->getResponse(),
      'sf_user' => $sf_context->getUser()
    );
    $variables = array_merge($variables, $vars);
    sfSympalConfig::set('template_vars', $variables);

    foreach ($variables as $name => $variable)
    {
      $$name = $variable;
    }

    ob_start();
    $code = str_replace('[?php', '<?php', $code);
    $code = str_replace('?]', '?>', $code);
    eval("?>" . $code);
    $rendered = ob_get_contents();
    ob_end_clean();

    $rendered = preg_replace_callback("/##(.*)\/(.*)##/", array('sfSympalToolkit', 'replaceSymfonyResources'), $rendered);

    return $rendered;
  }

  public static function replaceSymfonyResources($matches)
  {
    list($match, $module, $action) = $matches;
    $variables = sfSympalConfig::get('template_vars');
    
    return sfSympalToolkit::getSymfonyResource($module, $action, $variables);
  }

  public static function loadDefaultLayout()
  {
    return self::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public static function changeLayout($name)
  {
    if (!$name)
    {
      return false;
    }

    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $response = sfContext::getInstance()->getResponse();
    $configuration = $context->getConfiguration();

    if (sfSympalConfig::get('load_default_css'))
    {
      $response->addStylesheet('/sfSympalPlugin/css/global');
      $response->addStylesheet('/sfSympalPlugin/css/default');
    }

    $actionEntry = $context->getController()->getActionStack()->getLastEntry();
    $module = $actionEntry ? $actionEntry->getModuleName():$request->getParameter('module');
    $action = $actionEntry ? $actionEntry->getActionName():$request->getParameter('action');

    $paths = array(
      sfConfig::get('sf_app_dir'),
      sfConfig::get('sf_root_dir'),
      $configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir(),
    );

    $pluginPaths = $configuration->getAllPluginPaths();
    foreach ($pluginPaths as $pluginPath)
    {
      $paths[] = $pluginPath;
    }

    foreach ($paths as $path)
    {
      $path .= '/templates/'.$name;
      $checkPath = strstr($path, '.php') ? $path:$path.'.php';
      if (file_exists($checkPath))
      {
        $path = str_replace('.php', '', $path);
        break;
      }
    }

    $info = pathinfo($path);
    $name = $info['filename'];

    sfConfig::set('symfony.view.'.$module.'_'.$action.'_layout', $path);
    sfConfig::set('symfony.view.sympal_default_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_default_secure_layout', $path);

    if (strstr($path, 'sfSympalPlugin/templates'))
    {
      $path = '/sfSympalPlugin/css/' . $name;
    } else {
      if (file_exists(sfConfig::get('sf_web_dir').'/css/'.$name.'.css'))
      {
        $path = $name;
      } else {
        foreach ($pluginPaths as $plugin => $path)
        {
          if (file_exists($path.'/web/css/'.$name.'.css'))
          {
            $path = '/'.$plugin.'/css/'.$name.'.css';
            break;
          }
        }
      }
    }

    $response->removeStylesheet(sfSympalConfig::get('last_stylesheet'));

    sfSympalConfig::set('last_stylesheet', $path);
    $response->addStylesheet($path);

    return true;
  }

  public static function isEditMode()
  {
    $user = sfContext::getInstance()->getUser();

    return $user->isAuthenticated()  && $user->hasCredential('ManageContent') && $user->getAttribute('sympal_edit', true);
  }

  public static function generateBreadcrumbs($breadcrumbsArray)
  {
    $breadcrumbs = new sfSympalMenuBreadcrumbs('Breadcrumbs');

    $count = 0;
    $total = count($breadcrumbsArray);
    foreach ($breadcrumbsArray as $name => $route)
    {
      $count++;
      if ($count == $total)
      {
        $breadcrumbs->addChild($name);
      } else {
        $breadcrumbs->addChild($name, $route);
      }
    }

    return $breadcrumbs;
  }

  protected static $_contentTypesCache = null;

  public static function getContentTypesCache()
  {
    if (is_null(self::$_contentTypesCache))
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
      if (file_exists($cachePath))
      {
        self::$_contentTypesCache = unserialize(file_get_contents($cachePath));
      } else {
        self::$_contentTypesCache = array();
      }
    }

    return self::$_contentTypesCache;
  }

  protected static $_helperAutoloadCache = null;

  public static function autoloadHelper($functionName)
  {
    if (is_null(self::$_helperAutoloadCache))
    {
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal/helper_autoload.cache';
      if (file_exists($cachePath))
      {
        self::$_helperAutoloadCache = unserialize(file_get_contents($cachePath));
      } else {
        self::$_helperAutoloadCache = array();
      }
    }
    if (isset(self::$_helperAutoloadCache[$functionName]))
    {
      require_once(self::$_helperAutoloadCache[$functionName]);
    } else {
      throw new sfException('Could not autoload helper for function "'.$functionName.'"');
    }
  }

  public static function getAllLanguageCodes()
  {
    $flags = sfFinder::type('file')
      ->in(sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getRootDir().'/web/images/flags');

    $codes = array();
    foreach ($flags as $flag)
    {
      $info = pathinfo($flag);
      $codes[] = $info['filename'];
    }
    return $codes;
  }
}