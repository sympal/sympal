<?php

class sfSympalToolkit
{
  public static function loadHelpers($helpers)
  {
    sfApplicationConfiguration::getActive()->loadHelpers($helpers);
  }

  public static function useStylesheet($stylesheet, $position = 'last')
  {
    return sfContext::getInstance()->getResponse()->addStylesheet(sfSympalConfig::getAssetPath($stylesheet), $position);
  }

  public static function useJavascript($stylesheet, $position = 'last')
  {
    return sfContext::getInstance()->getResponse()->addJavascript(sfSympalConfig::getAssetPath($javascript), $position);
  }

  public static function useJQuery($plugins = array())
  {
    self::loadHelpers('jQuery');
    jq_add_plugins_by_name($plugins);
  }

  public static function renderException(Exception $e)
  {
    return get_partial('sympal_default/exception', array('e' => $e));
  }

  public static function getDefaultApplication()
  {
    $apps = glob(sfConfig::get('sf_root_dir').'/apps/*');
    foreach ($apps as $app)
    {
      $info = pathinfo($app);
      $path = $app.'/config/'.$info['filename'].'Configuration.class.php';
      require_once $path;
      $reflection = new ReflectionClass($info['filename'].'Configuration');
      if (!$reflection->getConstant('disableSympal'))
      {
        return $info['filename'];
      }
    }

    return 'sympal';
  }

  public static function checkRequirements()
  {
    $user = sfContext::getInstance()->getUser();
    $app = sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app'));
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

  public static function getSymfonyResource($module, $action = null, $variables = array())
  {
    if (strpos($module, '/'))
    {
      $variables = (array) $action;
      $e = explode('/', $module);
      list($module, $action) = $e;
    }

    $context = sfContext::getInstance();
    $context->getConfiguration()->loadHelpers('Partial');
    $controller = $context->getController();

    if ($controller->componentExists($module, $action))
    {
      return get_component($module, $action, $variables);
    } else {
      return get_partial($module.'/'.$action, $variables);
    }

    throw new sfException('Could not find component or partial for the module "'.$module.'" and action "'.$action.'"');
  }

  protected static $_helperAutoloadCache = null;

  public static function autoloadHelper($functionName)
  {
    if (is_null(self::$_helperAutoloadCache))
    {
      self::$_helperAutoloadCache = sfSympalContext::getInstance()->getSympalConfiguration()->getCache()->getHelperAutoload();
    }
    if (isset(self::$_helperAutoloadCache[$functionName]))
    {
      require_once(self::$_helperAutoloadCache[$functionName]);
    } else {
      throw new sfException('Could not autoload helper for function "'.$functionName.'"');
    }
  }

  public static function moduleAndActionExists($moduleName, $actionName)
  {
    $modulePath = sfConfig::get('sf_apps_dir').'/'.sfConfig::get('sf_app').'/modules/'.$moduleName.'/actions/actions.class.php';
    if (file_exists($modulePath))
    {
      return strpos(file_get_contents($modulePath), 'public function execute'.ucfirst($actionName)) !== false ? true : false;
    } else {
      return false;
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

  public static function getRedirectRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/redirect_routes.cache.yml';
    if (file_exists($cachePath) && sfConfig::get('sf_environment') !== 'test')
    {
      return file_get_contents($cachePath);
    }

    try {
      $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    id: %s
';

      $routes = array();
      $siteSlug = sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app'));

      $redirects = Doctrine::getTable('sfSympalRedirect')
        ->createQuery('r')
        ->innerJoin('r.Site s')
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($redirects as $redirect)
      {
        $routes[] = sprintf($routeTemplate,
          'sympal_redirect_'.$redirect->getId(),
          $redirect->getSource(),
          'sympal_redirecter',
          'index',
          $redirect->getId()
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);
      return $routes;
    } catch (Exception $e) { }
  }

  public static function getContentRoutesYaml()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/content_routes.cache.yml';
    if (file_exists($cachePath) && sfConfig::get('sf_environment') !== 'test')
    {
      return file_get_contents($cachePath);
    }

    try {
      $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    sf_format: html
    sympal_content_type: %s
    sympal_content_type_id: %s
    sympal_content_id: %s
  class: sfDoctrineRoute
  options:
    model: sfSympalContent
    type: object
    method: getContent
    allow_empty: true
    requirements:
      sf_culture:  (%s)
      sf_format:   (%s)
';

      $routes = array();
      $siteSlug = sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app'));

      $contents = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->innerJoin('c.Site s')
        ->where("c.custom_path IS NOT NULL AND c.custom_path != ''")
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($contents as $content)
      {
        $routes['content_'.$content->getId()] = sprintf($routeTemplate,
          substr($content->getRouteName(), 1),
          $content->getRoutePath(),
          $content->getModuleToRenderWith(),
          $content->getActionToRenderWith(),
          $content->Type->name,
          $content->Type->id,
          $content->id,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $contents = Doctrine::getTable('sfSympalContent')
        ->createQuery('c')
        ->leftJoin('c.Type t')
        ->innerJoin('c.Site s')
        ->where("c.module IS NOT NULL AND c.module != ''")
        ->orWhere("c.action IS NOT NULL AND c.action != ''")
        ->andWhere('s.slug = ?', $siteSlug)
        ->execute();

      foreach ($contents as $content)
      {
        $routes['content_'.$content->getId()] = sprintf($routeTemplate,
          substr($content->getRouteName(), 1),
          $content->getRoutePath(),
          $content->getModuleToRenderWith(),
          $content->getActionToRenderWith(),
          $content->Type->name,
          $content->Type->id,
          $content->id,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $contentTypes = Doctrine::getTable('sfSympalContentType')
        ->createQuery('t')
        ->execute();

      foreach ($contentTypes as $contentType)
      {
        $routes['content_type_'.$contentType->getId()] = sprintf($routeTemplate,
          substr($contentType->getRouteName(), 1),
          $contentType->getRoutePath(),
          $contentType->getModuleToRenderWith(),
          $contentType->getActionToRenderWith(),
          $contentType->name,
          $contentType->id,
          null,
          implode('|', sfSympalConfig::getLanguageCodes()),
          implode('|', sfSympalConfig::get('content_formats'))
        );
      }

      $routes = implode("\n", $routes);
      file_put_contents($cachePath, $routes);
      return $routes;
    } catch (Exception $e) {}
  }
}