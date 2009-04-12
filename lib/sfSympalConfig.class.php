<?php

class sfSympalConfig
{
  public static function get($group, $name = null, $default = null)
  {
    $default = is_null($default) ? false:$default;
    if (is_null($name))
    {
      return sfConfig::get('app_sympal_settings_'.$group, $default);
    } else {
      $settings = sfConfig::get('app_sympal_settings_'.$group);

      return isset($settings[$name]) ? $settings[$name]:$default;
    }
  }

  public static function set($group, $name = null, $value = null)
  {
    if (is_null($value))
    {
      return sfConfig::set('app_sympal_settings_'.$group, $name);
    } else {
      $settings = sfConfig::get('app_sympal_settings_'.$group);
      $settings[$name] = $value;
      sfConfig::set('app_sympal_settings_'.$group, $settings);
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

  public static function isVersioningEnabled($name = null)
  {
    if ($name)
    {
      if (is_object($name))
      {
        $name = get_class($name);
      }
      $versionedModels = self::get('versioned_models');
      return self::get('versioning') && isset($versionedModels[$name]);
    } else {
      return self::get('versioning');
    }
  }

  public static function getVersionedModelOptions($name)
  {
    if (self::isVersioningEnabled($name))
    {
      $versionedModels = self::get('versioned_models');
      return $versionedModels[$name];
    } else {
      return array();
    }
  }

  public static function writeSetting($group, $name, $value)
  {
    $path = sfConfig::get('sf_config_dir').'/app.yml';
    if (!file_exists($path))
    {
      touch($path);
    }
    $array = (array) sfYaml::load(file_get_contents($path));

    if (is_null($group))
    {
      $array['all']['sympal_settings'][$name] = $value;
    } else {
      $array['all']['sympal_settings'][$group][$name] = $value;
    }

    sfSympalConfig::set($group, $name, $value);

    file_put_contents($path, sfYaml::dump($array, 4));
  }
}