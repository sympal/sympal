<?php

class sfSympalDisableForAppTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application to delete'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev')
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'disable-for-app';
    $this->briefDescription = 'Disable Sympal for the specified application';

    $this->detailedDescription = <<<EOF
The [symfony sympal:disable-for-app|INFO] disables Sympal from being loaded for the specified app.

  [./symfony sympal:disable-for-app my_app|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('sympal', sprintf('Disabling Sympal for Symfony application named "%s"...', $arguments['application']));

    if ($this->isSympalEnabled())
    {
      $path = sfConfig::get('sf_app_dir').'/config/'.$arguments['application'].'Configuration.class.php';
      $find = sprintf('class %sConfiguration extends sfApplicationConfiguration
{', $arguments['application']);
      $replace = sprintf('class %sConfiguration extends sfApplicationConfiguration
{
  const disableSympal = true;
', $arguments['application']);
      $code = file_get_contents($path);
      $code = str_replace($find, $replace, $code);
      file_put_contents($path, $code);

      $this->logSection('sympal', '...disabling Sympal in application configuration class', null, 'COMMENT');
    }
    
    $this->logSection('sympal', '...reverting class myUser extends', null, 'COMMENT');

    $path = sfConfig::get('sf_app_dir').'/lib/myUser.class.php';
    $code = file_get_contents($path);
    $find = 'class myUser extends sfSympalUser';
    $replace = 'class myUser extends sfBasicSecurityUser';
    file_put_contents($path, str_replace($find, $replace, $code));

    $this->clearCache();
  }

  public function isSympalEnabled()
  {
    if ($application = sfConfig::get('sf_app'))
    {
      $reflection = new ReflectionClass($application.'Configuration');
      if ($reflection->getConstant('disableSympal'))
      {
        return false;
      }
    }
    return true;
  }
}