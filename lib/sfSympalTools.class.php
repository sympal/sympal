<?php
class sfSympalTools
{
  protected static
    $_currentMenuItem,
    $_currentEntity;

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
      if (isset($pluginConfiguration->dependencies) && !empty($pluginConfiguration->dependencies))
      {
        $requiredPlugins = array_merge($requiredPlugins, $pluginConfiguration->dependencies);
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

    $widget = new $class();
    $widget->setAttribute('id', 'entity_slot_value_' . $entitySlot['id']);
    $widget->setAttribute('onKeyUp', "edit_on_key_up('".$entitySlot['id']."');");

    $widgetSchema['value'] = $widget;
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

  public static function getCurrentSite()
  {
    return sfSympalConfig::get('current_site', null, sfSympalConfig::get('default_site'));
  }

  public static function setCurrentSite($currentSite)
  {
    sfSympalConfig::set('current_site', $currentSite);
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
    sfConfig::set('symfony.view.sympal_frontend_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_frontend_secure_layout', $path);

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
}