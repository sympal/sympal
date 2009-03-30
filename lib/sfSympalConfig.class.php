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
      return self::get('I18n', 'enabled') && self::get('I18n', $name);
    } else {
      return self::get('I18n', 'enabled');
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

    file_put_contents($path, sfYaml::dump($array, 4));
  }
}