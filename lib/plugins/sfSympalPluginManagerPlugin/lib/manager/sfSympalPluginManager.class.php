<?php

class sfSympalPluginManager
{
  protected
    $_name,
    $_pluginName,
    $_pluginPath,
    $_contentTypeName,
    $_configuration,
    $_dispatcher,
    $_formatter,
    $_filesystem,
    $_pluginConfig,
    $_pluginModels = array(),
    $_options = array();

  protected static
    $_lockFile = null;

  public function __construct($name, ProjectConfiguration $configuration = null, sfFormatter $formatter = null)
  {
    $this->_name = sfSympalPluginToolkit::getShortPluginName($name);
    $this->_pluginName = sfSympalPluginToolkit::getLongPluginName($this->_name);
    $this->_pluginPath = sfSympalPluginToolkit::getPluginPath($this->_pluginName);

    $schema = sfFinder::type('file')->name('*.yml')->in($this->_pluginPath.'/config/doctrine');
    $this->_pluginModels = array();
    foreach ($schema as $file)
    {
      $this->_pluginModels = array_merge($this->_pluginModels, array_keys(sfYaml::load($file)));
    }

    $this->_contentTypeName = $this->getContentTypeForPlugin($this->_pluginName);
    $this->_configuration = is_null($configuration) ? ProjectConfiguration::getActive():$configuration;
    $this->_dispatcher = $this->_configuration->getEventDispatcher();
    $this->_formatter = is_null($formatter) ? new sfFormatter():$formatter;
    $this->_filesystem = new sfFilesystem($this->_dispatcher, $this->_formatter);

    try {
      $this->_pluginConfig = $this->_configuration->getPluginConfiguration($this->_pluginName);
    } catch (Exception $e) {}
  }

  public static function getActionInstance($name, $action, ProjectConfiguration $configuration = null, sfFormatter $formatter = null)
  {
    if (is_null($name))
    {
      throw new sfException('You must speciy the plugin name you want to get the action instance for.');
    }

    $name = sfSympalPluginToolkit::getShortPluginName($name);
    $pluginName = sfSympalPluginToolkit::getLongPluginName($name);

    $class = $pluginName.ucfirst($action);

    if (!class_exists($class))
    {
      $class = 'sfSympalPluginManager'.ucfirst($action);
    }
    return new $class($pluginName, $configuration, $formatter);
  }

  public function hasWebDirectory()
  {
    return is_dir($this->_pluginPath.'/web');
  }

  public function hasModels()
  {
    return !empty($this->_pluginModels);
  }

  public function getPluginModels()
  {
    return $this->_pluginModels;
  }

  public function getPluginModelsInInsertOrder()
  {
    return Doctrine_Manager::connection()->unitOfWork->buildFlushTree($this->_pluginModels);
  }

  public function getPluginModelsInDeleteOrder()
  {
    return array_reverse($this->getPluginModelsInInsertOrder());
  }

  public function getPluginPath()
  {
    return $this->_pluginPath;
  }

  public function getOption($key)
  {
    return $this->_options[$key];
  }

  public function getOptions()
  {
    return $this->_options;
  }

  public function setOption($key, $value)
  {
    $this->_options[$key] = $value;
  }

  public function setOptions(array $options)
  {
    $this->_options = $options;
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    $this->_configuration->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->_formatter->formatSection($section, $message, $size, $style))));
  }

  protected function _setDoctrineProperties($obj, $properties)
  {
    foreach ($properties as $key => $value)
    {
      if ($value instanceof Doctrine_Record)
      {
        $this->logSection('sympal', sprintf('...setting "%s"', get_class($obj).'->'.$key.'='.$value), null, 'COMMENT');

        $obj->$key = $value;
        unset($properties[$key]);
      }
    }

    $obj->fromArray($properties, true);
  }

  public function newContent($contentType, $properties = array())
  {
    if (is_string($contentType))
    {
      $contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($contentType);
    }

    if (!$contentType instanceof sfSympalContentType)
    {
      throw new InvalidArgumentException('Invalid ContentType');
    }

    $content = new sfSympalContent();
    $content->Type = $contentType;
    $content->CreatedBy = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->findOneByIsSuperAdmin(1);
    $content->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')));
    $content->date_published = new Doctrine_Expression('NOW()');

    $name = $contentType['name'];
    $content->$name = new $name();

    $content->trySettingTitleProperty('Sample '.$contentType['label']);

    $this->_setDoctrineProperties($content, $properties);
    
    $this->logSection('sympal', sprintf('...instantiating new %s "%s"', $contentType->getLabel(), $content), null, 'COMMENT');

    return $content;
  }

  public function newContentType($name, $properties = array())
  {
    $contentType = new sfSympalContentType();
    $contentType->name = $name;
    $contentType->label = sfInflector::humanize(sfInflector::tableize(str_replace('sfSympal', null, $name)));
    $contentType->slug = Doctrine_Inflector::urlize($contentType->label);
    $contentType->default_path = '/'.$contentType->slug.'/:slug';

    $this->_setDoctrineProperties($contentType, $properties);

    $this->logSection('sympal', sprintf('...instantiating new content type "%s"', $contentType), null, 'COMMENT');

    return $contentType;
  }

  public function newMenuItem($name, $properties = array())
  {
    $menuItem = new sfSympalMenuItem();
    $menuItem->name = $name;
    $menuItem->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug(sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')));
    $menuItem->date_published = new Doctrine_Expression('NOW()');

    $this->_setDoctrineProperties($menuItem, $properties);

    $this->logSection('sympal', sprintf('...instantiating new menu item "%s"', $menuItem->getLabel()), null, 'COMMENT');

    return $menuItem;
  }

  public function saveMenuItem(sfSympalMenuItem $menuItem)
  {
    $this->logSection('sympal', sprintf('...saving menu item "%s"', $menuItem));

    if ($menu = Doctrine_Core::getTable('sfSympalMenuItem')->getPluginInstallMenu())
    {
      $this->logSection('sympal', sprintf('...inserting as last child of "%s"', $menu->getLabel()));
      $menuItem->getNode()->insertAsLastChildOf($menu);
    }
  }

  public function getContentTypeForPlugin($name = null)
  {
    $name = $this->_pluginName;
    try {
      $pluginName = sfSympalPluginToolkit::getLongPluginName($name);
      $path = ProjectConfiguration::getActive()->getPluginConfiguration($pluginName)->getRootDir();
      $files = glob($path.'/config/doctrine/*.yml');

      if (!empty($files))
      {
        $array = array();
        foreach ($files as $file)
        {
          $array = array_merge($array, (array) sfYaml::load($file));
        }

        foreach ($array as $modelName => $model)
        {
          if (isset($model['actAs']) && !empty($model['actAs']))
          {
            foreach ($model['actAs'] as $key => $value)
            {
              if (is_numeric($key))
              {
                $name = $value;
              } else {
                $name = $key;
              }
              if ($name == 'sfSympalContentTypeTemplate')
              {
                return $modelName;
              }
            }
          }
        }
      }
    } catch (Exception $e) {}

    return false;
  }

  protected function _buildAllClasses()
  {
    $this->logSection('sympal', '...building all classes', null, 'COMMENT');

    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineBuildTask($this->_dispatcher, $this->_formatter);
    $task->run(array(), array('all-classes', '--application='.sfConfig::get('sf_app')));
  }

  protected function _clearCache()
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfCacheClearTask($this->_dispatcher, $this->_formatter);
    $task->run();
  }

  protected function _publishAssets()
  {
    if ($this->hasWebDirectory())
    {
      chdir(sfConfig::get('sf_root_dir'));
      $assets = new sfPluginPublishAssetsTask($this->_dispatcher, $this->_formatter);
      $ret = $assets->run(array($this->_pluginName), array());
    }
  }
}