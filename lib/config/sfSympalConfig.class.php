<?php

/**
 * Extension of sfConfig to allow us to add some shortcuts and build on top of the 
 * normal sfConfig
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalConfig extends sfConfig
{
  /**
   * Get the array of searchable models
   *
   * @return array $models
   */
  public static function getSearchableModels()
  {
    return array('sfSympalContent');
  }

  /**
   * Check whether a Doctrine query result cache key should use result cache or not
   *
   * @param string $key 
   * @return boolean
   */
  public static function shouldUseResultCache($key)
  {
    if (isset(self::$config['app_sympal_config_orm_cache']['queries'][$key]['enabled'])
      && self::$config['app_sympal_config_orm_cache']['queries'][$key]['enabled']
      && isset(self::$config['app_sympal_config_orm_cache']['result'])
      && self::$config['app_sympal_config_orm_cache']['result']
    )
    {
      return isset(self::$config['app_sympal_config_orm_cache']['queries'][$key]['lifetime']) ? self::$config['app_sympal_config_orm_cache']['queries'][$key]['lifetime'] : self::$config['app_sympal_config_orm_cache']['lifetime'];
    } else {
      return false;
    }
  }

  /**
   * Get the array of language codes for i18n
   *
   * @return array $languageCodes
   */
  public static function getLanguageCodes()
  {
    return !empty(self::$config['app_sympal_config_language_codes']) ? self::$config['app_sympal_config_language_codes'] : array();
  }

  /**
   * Get a setting value
   *
   * @param string $group 
   * @param string $name 
   * @param string $default 
   * @return mixed $value
   */
  public static function get($group, $name = null, $default = null)
  {
    $default = $default === null ? false : $default;
    if ($name === null)
    {
      return isset(self::$config['app_sympal_config_'.$group]) ? self::$config['app_sympal_config_'.$group] : $default;
    } else {
      return isset(self::$config['app_sympal_config_'.$group][$name]) ? self::$config['app_sympal_config_'.$group][$name] : $default;
    }
  }

  /**
   * Set a setting value
   *
   * @param string $group 
   * @param string $name 
   * @param string $value 
   * @return void
   */
  public static function set($group, $name, $value = null)
  {
    if (is_null($value))
    {
      self::$config['app_sympal_config_'.$group] = $name;
    } else {
      self::$config['app_sympal_config_'.$group][$name] = $value;
    }
  }

  /**
   * Check if i18n is enabled globally or for a given model
   *
   * @param string $name Optional name of the model to check for i18n on
   * @return boolean
   */
  public static function isI18nEnabled($name = null)
  {
    if (empty(self::$config['app_sympal_config_language_codes']))
    {
      return false;
    }

    if ($name)
    {
      if (is_object($name))
      {
        $name = get_class($name);
      }
      return isset(self::$config['app_sympal_config_i18n']) && self::$config['app_sympal_config_i18n'] && isset(self::$config['app_sympal_config_internationalized_models'][$name]);
    } else {
      return isset(self::$config['app_sympal_config_i18n']) && self::$config['app_sympal_config_i18n'];
    }
  }

  /**
   * Get the current installed version of Sympal
   *
   * @return string $version
   */
  public static function getCurrentVersion()
  {
    return isset(self::$config['app_sympal_config_asset_paths']['current_version']) ? self::$config['app_sympal_config_asset_paths']['current_version'] : sfSympalPluginConfiguration::VERSION;
  }

  /**
   * Get a overridden asset path or return the original asset path
   *
   * @param string $path 
   * @return string $path
   */
  public static function getAssetPath($path)
  {
    return isset(self::$config['app_sympal_config_asset_paths'][$path]) ? self::$config['app_sympal_config_asset_paths'][$path] : $path;
  }

  /**
   * Get name of the admin generator theme to use
   *
   * @return string $theme
   */
  public static function getAdminGeneratorTheme()
  {
    $theme = sfSympalConfig::get('themes', sfSympalConfig::get('admin_theme'));
    return isset($theme['admin_generator_theme']) ? $theme['admin_generator_theme'] : sfSympalConfig::get('default_admin_generator_theme', null, 'sympal_admin');
  }

  /**
   * Get the name of the admin generator class to use
   *
   * @return string $class
   */
  public static function getAdminGeneratorClass()
  {
    $theme = sfSympalConfig::get('themes', sfSympalConfig::get('admin_theme'));
    return isset($theme['admin_generator_class']) ? $theme['admin_generator_class'] : sfSympalConfig::get('default_admin_generator_class', null, 'sfSympalDoctrineGenerator');
  }

  /**
   * Write a setting to the config/app.yml. The api of this is the same as set()
   *
   * @param string $group 
   * @param string $name 
   * @param string $value 
   * @param string $application Whether or not to write this setting to the app config file
   * @return void
   */
  public static function writeSetting($group, $name, $value = null, $application = false)
  {
    if (is_null($value))
    {
      $value = $name;
      $name = $group;
      $group = null;
    }

    if ($application)
    {
      $path = sfConfig::get('sf_app_dir').'/config/app.yml';
    } else {
      $path = sfConfig::get('sf_config_dir').'/app.yml';
    }

    if (!file_exists($path))
    {
      touch($path);
    }
    $array = (array) sfYaml::load(file_get_contents($path));

    if (is_null($group))
    {
      $array['all']['sympal_config'][$name] = $value;
    } else {
      $array['all']['sympal_config'][$group][$name] = $value;
    }

    self::set($group, $name, $value);

    file_put_contents($path, sfYaml::dump($array, 4));
  }
}