<?php

class sfSympalPluginManager
{
  public $formatter;

  public function __construct()
  {
    $this->configuration = ProjectConfiguration::getActive();
    $this->dispatcher = $this->configuration->getEventDispatcher();
    $this->formatter = new sfFormatter();
    $this->filesystem = new sfFilesystem($this->dispatcher, $this->formatter);
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    ProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($section, $message, $size, $style))));
  }

  public function getEntityTypeForPlugin($name)
  {
    $pluginName = sfSympalTools::getLongPluginName($name);
    $path = ProjectConfiguration::getActive()->getPluginConfiguration($pluginName)->getRootDir();
    $schema = $path.'/config/doctrine/schema.yml';

    if (file_exists($schema))
    {
      $array = (array) sfYaml::load($schema);
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
            if ($name == 'sfSympalEntityType')
            {
              return $modelName;
            }
          }
        }
      }
    }
    return false;
  }
}