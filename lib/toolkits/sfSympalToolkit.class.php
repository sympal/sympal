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
      self::$_currentSite =  Doctrine_Core::getTable('Site')
        ->createQuery('s')
        ->where('s.slug = ?', sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')))
        ->fetchOne();
    }
    return self::$_currentSite;
  }

  public static function getCurrentSiteId()
  {
    return self::getCurrentSite()->id;
  }

  public static function getDefaultApplication()
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

  public static function isEditMode()
  {
    $user = sfContext::getInstance()->getUser();

    return $user->isAuthenticated()  && $user->hasCredential('ManageContent');
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

  public static function getRoutesYaml()
  {
    $routeTemplate =
'%s:
  url:   %s
  param:
    module: %s
    action: %s
    sf_format: html
    sympal_content_type: %s
    sympal_content_id: %s
  class: sfDoctrineRoute
  options:
    model: %s
    type: object
    method: getContent
    requirements:
      sf_culture:  (%s)
      sf_format:   (%s)
';

    $contents = Doctrine::getTable('Content')
      ->createQuery('c')
      ->leftJoin('c.Type t')
      ->execute();

    $routes = array();
    foreach ($contents as $content)
    {
      $routes[] = sprintf($routeTemplate,
        substr($content->getRouteName(), 1),
        $content->getRoutePath(),
        'sympal_content_renderer',
        'index',
        $content->Type->name,
        $content->id,
        'Content',
        implode('|', sfSympalConfig::get('language_codes')),
        implode('|', sfSympalConfig::get('content_formats'))
      );
    }

    return implode("\n", $routes);
  }
}