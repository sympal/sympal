<?php
class sfSympalTools
{
  protected static
    $_currentMenuItem,
    $_currentEntity;

  public static
    $mailer,
    $emailAddress;

  public static function getCurrentMenuItem()
  {
    return self::$_currentMenuItem;
  }

  public static function setCurrentMenuItem(MenuItem $menuItem)
  {
    self::$_currentMenuItem = $menuItem;
  }

  public static function getCurrentEntity()
  {
    return self::$_currentEntity;
  }

  public static function setCurrentEntity(Entity $entity)
  {
    self::$_currentEntity = $entity;
  }

  public static function checkPluginDependencies($pluginConfiguration, $dependencies)
  {
    $context = sfContext::getInstance();
    $configuration = $context->getConfiguration();

    $plugins = $configuration->getPlugins();

    $dependencies = (array) $dependencies;
    foreach ($dependencies as $dependency)
    {
      if (!in_array($dependency, $plugins))
      {
        throw new sfException(
          sprintf(
            'Dependency check failed for "%s". Missing plugin named "%s".'."\n\n".
            'The following plugins are required: %s',
            $pluginConfiguration->getName(),
            $dependency,
            implode(', ', $dependencies)
          )
        );
      }
    }
  }

  public static function getRequiredPlugins()
  {
    $requiredPlugins = array();

    $context = sfContext::getInstance();
    $configuration = $context->getConfiguration();

    $plugins = $configuration->getPlugins();
    foreach ($plugins as $plugin)
    {
      $pluginConfiguration = $configuration->getPluginConfiguration($plugin);
      $refClass = new ReflectionClass($pluginConfiguration);
      $dependencies = $refClass->getStaticPropertyValue('dependencies');
      if (isset($dependencies) && !empty($dependencies))
      {
        $requiredPlugins = array_merge($requiredPlugins, $dependencies);
      }
    }

    $requiredPlugins = array_unique($requiredPlugins);

    return $requiredPlugins;
  }

  public static function renderContent($content, $variables = array())
  {
    $sf_context = sfContext::getInstance();
    $vars = array(
      'sf_request' => $sf_context->getRequest(),
      'sf_response' => $sf_context->getResponse(),
      'sf_user' => $sf_context->getUser()
    );
    $variables = array_merge($variables, $vars);
    foreach ($variables as $name => $variable)
    {
      $$name = $variable;
    }

    ob_start();
    $content = str_replace('[?php', '<?php', $content);
    $content = str_replace('?]', '?>', $content);
    eval("?>" . $content);
    $rendered = ob_get_contents();
    ob_end_clean();

    return $rendered;
  }

  public static function embedI18n($name, sfFormDoctrine $form)
  {
    if (sfSympalConfig::isI18nEnabled($name))
    {
      $culture = sfContext::getInstance()->getUser()->getCulture();
      $form->embedI18n(array(strtolower($culture)));
    }
  }

  public static function changeEntitySlotValueWidget($entitySlot, $form)
  {
    $widgetSchema = $form->getWidgetSchema();
    $type = $entitySlot->Type;

    $class = 'sfWidgetFormSympal'.$type->name;

    if (!class_exists($class))
    {
      throw new sfException('You must create a Sympal form widget class named '.$class);
    }

    $widget = new $class();
    $widget->setAttribute('id', 'entity_slot_value_' . $entitySlot['id']);
    $widget->setAttribute('onKeyUp', "edit_on_key_up('".$entitySlot['id']."');");

    $widgetSchema['value'] = $widget;

    $class = 'sfValidatorFormSympal'.$type->name;

    if (!class_exists($class))
    {
      $class = 'sfValidatorPass';
    }

    $validator = new $class;
    $validatorSchema = $form->getValidatorSchema();
    $validatorSchema['value'] = $validator;
  }

  public static function changeLayoutWidget($form)
  {
    $layouts = sfContext::getInstance()->getConfiguration()->getPluginConfiguration('sfSympalPlugin')->getSympalConfiguration()->getLayouts();
    array_unshift($layouts, '');
    $form->setWidget('layout', new sfWidgetFormChoice(array(
      'choices'   => $layouts
    )));

    $form->setValidator('layout', new sfValidatorChoice(array(
      'choices'   => array_keys($layouts)
    )));
  }

  public static function changeLayout($name)
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $response = sfContext::getInstance()->getResponse();
    $configuration = $context->getConfiguration();

    $bundledLayout = false;
    if (file_exists($name))
    {
      $fullPath = $name;
    } else if (file_exists($path = sfConfig::get('sf_app_dir').'/templates/'.$name.'.php')) {
      $fullPath = $path;
    } else {
      $path = $configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir() . '/templates/' . $name;
      $bundledLayout = true;
    }

    if (isset($fullPath) && file_exists($fullPath))
    {
      $e = explode('.', $fullPath);
      $path = $e[0];
      $info = pathinfo($fullPath);
      $name = $info['filename'];
    }

    sfConfig::set('symfony.view.'.$request->getParameter('module').'_'.$request->getParameter('action').'_layout', $path);
    sfConfig::set('symfony.view.sympal_default_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_default_secure_layout', $path);

    if ($bundledLayout)
    {
      $response->addStylesheet('/sfSympalPlugin/css/global');
      $response->addStylesheet('/sfSympalPlugin/css/default');
      $response->addStylesheet('/sfSympalPlugin/css/' . $name);
    } else {
      $response->addStylesheet($name, 'last');
    }
  }

  public static function isEditMode()
  {
    $user = sfContext::getInstance()->getUser();

    return $user->isAuthenticated() && $user->getAttribute('sympal_edit', false);
  }

  public static function isPluginInstalled($plugin)
  {
    try {
      sfContext::getInstance()->getConfiguration()->getPluginConfiguration($plugin);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public static function sendEmail($name, $vars = array())
  {
    $e = explode('/', $name);
    list($module, $action) = $e;

    try {
      $rawEmail = self::getEmailPresentationFor($module, $action, $vars);
    } catch (Exception $e) {
      throw new sfException('Could not send email: '.$e->getMessage());
    }

    if ($rawEmail)
    {
      $e = explode("\n", $rawEmail);
      
      $emailSubject = $e[0];
      unset($e[0]);
      $emailBody = implode("\n", $e);
    } else {
      $emailSubject = '';
      $emailBody = '';
    }

    $mailer = new Swift(new Swift_Connection_NativeMail());
    $message = new Swift_Message($emailSubject, $emailBody, 'text/html');

    $mailer->send($message, $vars['email_address'], sfSympalConfig::get('default_from_email_address', null, 'jonwage@gmail.com'));
    $mailer->disconnect();

    sfContext::getInstance()->getLogger()->debug($emailBody);
  }

  public static function getEmailPresentationFor($module, $action, $vars = array())
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Partial'));

    try {
      return get_partial($module.'/'.$action, $vars);
    } catch (Exception $e1) {
      try {
        return get_component($module, $action, $vars);
      } catch (Exception $e2) {
        throw new sfException('Could not find a partial or component for '.$module.' and '.$action.': '.$e1->getMessage().' '.$e2->getMessage());
      }
    }
  }

  public static function getLongPluginName($name)
  {
    if (strstr($name, 'sfSympal'))
    {
      return $name;
    } else {
      return 'sfSympal'.Doctrine_Inflector::classify(Doctrine_Inflector::tableize($name)).'Plugin';
    }
  }

  public static function getShortPluginName($name)
  {
    if (strstr($name, 'sfSympal'))
    {
      return substr($name, 8, strlen($name) - 14);
    } else {
      return Doctrine_Inflector::classify(Doctrine_Inflector::tableize($name));
    }
  }

  public static function getAvailablePluginPaths()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal_available_plugins.cache';
    if (!file_exists($cachePath))
    {
      $installedPlugins = ProjectConfiguration::getActive()->getPlugins();

      $available = array();
      $paths = sfSympalConfig::get('sympal_plugin_svn_sources');

      foreach ($paths as $path)
      {
        if (is_dir($path))
        {
          $find = sfFinder::type('dir')->maxdepth(1)->name('sfSympal*Plugin')->in($path);
          foreach ($find as $p)
          {
            $info = pathinfo($p);
            $available[$info['basename']] = $p;
          }
        } else {
          $html = file_get_contents($path);
          preg_match_all("/sfSympal(.*)Plugin/", strip_tags($html), $matches);
          foreach ($matches[0] as $plugin)
          {
            $available[$plugin] = $path;
          }
        }
      }

      $diff = array_diff(array_keys($available), $installedPlugins);
      $avail = array();
      foreach ($diff as $plugin)
      {
        $avail[$plugin] = $available[$plugin];
      }
      $cachePath = sfConfig::get('sf_cache_dir').'/sympal_available_plugins.cache';
      file_put_contents($cachePath, serialize($avail));
    } else {
      $content = file_get_contents($cachePath);
      $avail = unserialize($content);
    }
    return $avail;
  }

  public static function getAvailablePlugins()
  {
    return array_keys(self::getAvailablePluginPaths());
  }

  public static function getPluginDownloadPath($name)
  {
    $name = self::getShortPluginName($name);
    $pluginName = self::getLongPluginName($name);

    $e = explode('.', SYMFONY_VERSION);
    $version = $e[0].'.'.$e[1];

    $paths = self::getAvailablePluginPaths();
    $path = '';
    foreach ($paths as $pluginName => $path)
    {
      $branchSvnPath = $path.'/'.$pluginName.'/branches/'.$version;
      $trunkSvnPath = $path.'/'.$pluginName.'/trunk';
      if (@file_get_contents($branchSvnPath) !== false)
      {
        $path = $branchSvnPath;
      } else if (@file_get_contents($trunkSvnPath) !== false) {
        $path = $trunkSvnPath;
      }
    }

    if ($path)
    {
      return $path;
    } else {
      throw new sfException('Could not find SVN path for '.$pluginName);
    }
  }

  public static function isPluginAvailable($name)
  {
    $pluginName = self::getLongPluginName($name);
    $availablePlugins = self::getAvailablePlugins();
    return in_array($availablePlugins, $pluginName);
  }
}