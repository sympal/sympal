<?php

class sfSympalConfigureTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('configure', sfCommandArgument::IS_ARRAY, 'Set some Sympal configuration options.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'configure';
    $this->briefDescription = 'Set a Sympal configuration setting.';

    $this->detailedDescription = <<<EOF
The [symfony sympal:configure|INFO] task allows you to set Sympal configuration settings.

  [./symfony sympal:configure layout=sympal i18n=false|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    foreach ($arguments['configure'] as $value)
    {
      list($key, $value) = explode('=', $value);
      $e = explode('.', $key);
      $group = isset($e[1]) ? $e[0] : null;
      $key = isset($e[1]) ? $e[1] : $e[0];

      $value = is_numeric($value) ? (int) $value : $value;
      $value = $value == 'false' ? false : $value;
      $value = $value == 'true' ? true : $value;
      $writeToApp = isset($options['application']) && $options['application'] ? true : false;

      if ($group)
      {
        $this->logSection('sympal', sprintf('Writing setting "%s" with a value of "%s" under the "%s" group.', $key, $value, $group));

        sfSympalConfig::writeSetting($group, $key, $value, $writeToApp);
      } else {
        $this->logSection('sympal', sprintf('Writing setting "%s" with a value of "%s".', $key, $value, $group));

        sfSympalConfig::writeSetting(null, $key, $value, $writeToApp);
      }
    }
  }
}