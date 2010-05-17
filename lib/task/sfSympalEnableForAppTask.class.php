<?php

/**
 * Enables sympal for a particular application
 * 
 * @package     
 * @subpackage  
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalEnableForAppTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The symfony application to prepare as a sympal site.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'enable-for-app';
    $this->briefDescription = 'Prepare a symfony application to be a sympal site';

    $this->detailedDescription = <<<EOF
The [sympal:prepare-application|INFO] task will prepare a symfony application to be a sympal site

Specifically, this does the following

  * Makes sure there is not a "const disableSympal" in the application configuration
  * Make myUser extend sfSympalUser
  * Removes the default routes (homepage, default, default_index)

  [./sympal:prepare-application sympal|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('sympal', sprintf('Preparing symfony application named "%s" for sympal...', $arguments['application']));

    $path = sfConfig::get('sf_app_dir').'/config/'.$arguments['application'].'Configuration.class.php';
    $find = '  const disableSympal = true;';
    $replace = '';
    $code = file_get_contents($path);
    $code = str_replace($find, $replace, $code);
    file_put_contents($path, $code);

    $this->logSection('sympal', '...making sure sympal is not disabled for this application', null, 'COMMENT');

    $path = sfConfig::get('sf_apps_dir').'/'.$arguments['application'];

    $code = file_get_contents($path.'/lib/myUser.class.php');
    file_put_contents($path.'/lib/myUser.class.php', str_replace('class myUser extends sfBasicSecurityUser',  'class myUser extends sfSympalUser', $code));
    $this->logSection('sympal', '...modifying myUser to extends sfSympalUser', null, 'COMMENT');
    
    $this->removeRoutes();

    $this->clearCache();
  }
  
  /**
   * Removes some default routes from the routing.yml file
   * 
   * This removes the following routes:
   *   * homepage (only if module=default)
   *   * default
   *   * default_index
   */
  protected function removeRoutes()
  {
    $this->logSection('sympal', sprintf('...removing default routes for app "%s"', sfConfig::get('sf_app')), null, 'COMMENT');
    $path = sfConfig::get('sf_app_dir').'/config/routing.yml';
    
    // don't do anything if the routing.yml file has been removed
    if (file_exists($path))
    {
      $routes = sfYaml::load($path);
      $changed = false;

      if (isset($routes['homepage']))
      {
        // don't remove the homepage route if it appears to be used
        if ($routes['homepage']['param']['module'] == 'default')
        {
          $this->logSection('sympal', 'Removing "homepage" route.');
          $this->logSection('sympal', '    Unless you choose to override it in the application\'s app.yml,');
          $this->logSection('sympal', '    the "homepage" route is specified inside sympal, and renders an');
          $this->logSection('sympal', '    sfSympalContent object whose slug is slug is "home"');

          unset($routes['homepage']);
          $changed = true;
        }
      }
      
      if (isset($routes['default']))
      {
        $this->logSection('sympal', 'Removing route "default"');
        unset($routes['default']);
        $changed = true;
      }
      if (isset($routes['default_index']))
      {
        $this->logSection('sympal', 'Removing route "default_index"');
        unset($routes['default_index']);
        $changed = true;
      }
      
      if ($changed)
      {
        $this->logSection('sympal', 'Writing modified routing file to '.$path);
        file_put_contents($path, sfYaml::dump($routes));
      }
    }
  }
}