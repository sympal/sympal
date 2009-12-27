<?php

class sfSympalConfig
{
  public static function get($group, $name = null, $default = null)
  {
    $default = is_null($default) ? false:$default;
    if (is_null($name))
    {
      return sfConfig::get('app_sympal_config_'.$group, $default);
    } else {
      $settings = sfConfig::get('app_sympal_config_'.$group);

      return isset($settings[$name]) ? $settings[$name]:$default;
    }
  }

  public static function set($group, $name, $value = null)
  {
    if (is_null($value))
    {
      return sfConfig::set('app_sympal_config_'.$group, $name);
    } else {
      $settings = sfConfig::get('app_sympal_config_'.$group);
      $settings[$name] = $value;
      sfConfig::set('app_sympal_config_'.$group, $settings);
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
      $i18nedModels = self::get('internationalized_models');
      return self::get('i18n') && isset($i18nedModels[$name]);
    } else {
      return self::get('i18n');
    }
  }

  public static function getCurrentVersion()
  {
    return self::get('current_version', null, sfSympalPluginConfiguration::VERSION);
  }

  public static function writeSetting($group, $name, $value = null)
  {
    if (is_null($value))
    {
      $value = $name;
      $name = $group;
      $group = null;
    }

    $path = sfConfig::get('sf_config_dir').'/app.yml';
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

    if (is_null($group))
    {
      sfSympalConfig::set($name, $value);
    } else {
      sfSympalConfig::set($group, $name, $value);
    }

    file_put_contents($path, sfYaml::dump($array, 4));
  }
}