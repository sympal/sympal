<?php

/**
 * Toolkit class for general Sympal helper methods
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalToolkit
{
  /**
   * Helper method for getting file contents from http locations without using file_get_contents()
   *
   * @param string $file 
   * @return string $contents
   */
  public static function fileGetContents($file)
  {
    if (substr($file, 0, 4) === 'http')
    {
      $ch = curl_init();
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_URL, $file);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	$data = curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      if (curl_errno($ch) || !$data || $code == 404)
    	{
    	  return false;
    	} else {
      	curl_close($ch);
      	return $data;
      }
    } else {
      if (file_exists($file))
      {
        return file_get_contents($file);
      } else {
        return false;
      }
    }
  }

  /**
   * Load the given helpers
   *
   * @param string $helpers 
   * @return void
   */
  public static function loadHelpers($helpers)
  {
    sfApplicationConfiguration::getActive()->loadHelpers($helpers);
  }

  /**
   * Use the given stylesheet
   *
   * @param string $stylesheet 
   * @param string $position 
   * @return void
   */
  public static function useStylesheet($stylesheet, $position = 'last')
  {
    return sfContext::getInstance()->getResponse()->addStylesheet(sfSympalConfig::getAssetPath($stylesheet), $position);
  }

  /**
   * Use the given javascript
   *
   * @param string $stylesheet 
   * @param string $position 
   * @return void
   */
  public static function useJavascript($stylesheet, $position = 'last')
  {
    return sfContext::getInstance()->getResponse()->addJavascript(sfSympalConfig::getAssetPath($javascript), $position);
  }

  /**
   * Use jQuery in your project
   *
   * @param array $plugins Optional array of jQuery plugins to load
   * @return void
   */
  public static function useJQuery($plugins = array())
  {
    self::loadHelpers('jQuery');
    jq_add_plugins_by_name($plugins);
  }

  /**
   * Render a formatted exception message
   *
   * @param Exception $e 
   * @return string $html
   */
  public static function renderException(Exception $e)
  {
    return get_partial('sympal_default/exception', array('e' => $e));
  }

  /**
   * Get the default application by find the first app in the apps directory
   *
   * @return string $appName
   */
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

  /**
   * Check all the requirements for installing Sympal
   *
   * @return void
   * @throws sfException if a requirement is not met
   */
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

  /**
   * Get a symfony resource (partial or component)
   * 
   * This basically looks first for a component defined by the given module
   * and action. If one doesn't exist, it then looks for a partial matching
   * the module and action pair.
   *
   * @param string $module 
   * @param string $action 
   * @param array $variables 
   * @return string $html
   */
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

  /**
   * Autoload the helper file for a helper function
   *
   * @param string $functionName
   * @return void
   */
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

  /**
   * Check if a module and action exist
   *
   * @param string $moduleName 
   * @param string $actionName 
   * @return void
   * @author Jonathan Wage
   */
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

  /**
   * Get all available language codes/flags
   *
   * @return array $codes
   */
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

  /**
   * Get the redirect routes yaml for the routing.yml
   *
   * @return string $yaml
   */
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
      $siteSlug = sfConfig::get('sf_app');

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

  /**
   * Get the content routes yaml
   *
   * @return string $yaml
   */
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
      sf_method:   [post, get]
';

      $routes = array();
      $siteSlug = sfConfig::get('sf_app');

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