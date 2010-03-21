<?php

class sfSympalEnableForAppTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The Symfony application to prepare as a Sympal site.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'enable-for-app';
    $this->briefDescription = 'Prepare a Symfony application to be a Sympal site';

    $this->detailedDescription = <<<EOF
The [sympal:prepare-application|INFO] task will prepare a Symfony application to be a Sympal site

  [./sympal:prepare-application sympal|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('sympal', sprintf('Preparing Symfony application named "%s" for Sympal...', $arguments['application']));

    $path = sfConfig::get('sf_app_dir').'/config/'.$arguments['application'].'Configuration.class.php';
    $find = '  const disableSympal = true;';
    $replace = '';
    $code = file_get_contents($path);
    $code = str_replace($find, $replace, $code);
    file_put_contents($path, $code);

    $this->logSection('sympal', '...making sure Sympal is not disabled for this application', null, 'COMMENT');

    $path = sfConfig::get('sf_apps_dir').'/'.$arguments['application'];

    $code = file_get_contents($path.'/lib/myUser.class.php');
    file_put_contents($path.'/lib/myUser.class.php', str_replace('class myUser extends sfBasicSecurityUser',  'class myUser extends sfSympalUser', $code));
    $this->logSection('sympal', '...modifying myUser to extends sfSympalUser', null, 'COMMENT');
    
    $this->removeRoutes();

    $this->clearCache();
  }
  
  /**
   * Removes excess routes from the app's routing.yml file
   */
  protected function removeRoutes()
  {
    $this->logSection('sympal', sprintf('...removing default routes for app "%s"', sfConfig::get('sf_app')), null, 'COMMENT');
    $path = sfConfig::get('sf_app_dir').'/config/routing.yml';
    
    // don't do anything if the routing.yml file has been removed
    if (file_exists($path))
    {
      $array = sfYaml::load($path);
      unset($array['homepage'], $array['default'], $array['default_index']);
      file_put_contents($path, sfYaml::dump($array));
    }
  }
}