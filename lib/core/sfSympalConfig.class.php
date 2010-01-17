<?php

class sfSympalConfig extends sfConfig
{
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

  public static function set($group, $name, $value = null)
  {
    if (is_null($value))
    {
      return self::$config['app_sympal_config_'.$group] = $name;
    } else {
      return self::$config['app_sympal_config_'.$group][$name] = $value;
    }
  }

  public static function isI18nEnabled($name = null)
  {
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

  public static function getCurrentVersion()
  {
    return isset(self::$config['app_sympal_config_asset_paths']['current_version']) ? self::$config['app_sympal_config_asset_paths']['current_version'] : sfSympalPluginConfiguration::VERSION;
  }

  public static function getAssetPath($path)
  {
    return isset(self::$config['app_sympal_config_asset_paths'][$path]) ? self::$config['app_sympal_config_asset_paths'][$path] : $path;
  }

  public static function getAdminGeneratorTheme()
  {
    $theme = sfSympalConfig::get('themes', sfSympalConfig::get('admin_theme'));
    return isset($theme['admin_generator_theme']) ? $theme['admin_generator_theme'] : sfSympalConfig::get('default_admin_generator_theme', null, 'sympal_admin');
  }

  public static function getAdminGeneratorClass()
  {
    $theme = sfSympalConfig::get('themes', sfSympalConfig::get('admin_theme'));
    return isset($theme['admin_generator_class']) ? $theme['admin_generator_class'] : sfSympalConfig::get('default_admin_generator_class', null, 'sfSympalDoctrineGenerator');
  }

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