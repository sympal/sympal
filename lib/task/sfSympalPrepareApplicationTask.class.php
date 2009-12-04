<?php

class sfSympalPrepareApplicationTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The Symfony application to prepare as a Sympal site.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'prepare-application';
    $this->briefDescription = 'Prepare a Symfony application to be a Sympal site';

    $this->detailedDescription = <<<EOF
The [sympal:prepare-application|INFO] task will prepare a Symfony application to be a Sympal site

  [./sympal:prepare-application sympal|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $path = sfConfig::get('sf_apps_dir').'/'.$arguments['application'];

    file_put_contents(sfConfig::get('sf_apps_dir').'/'.$arguments['application'].'/config/routing.yml', '');
    $this->logSection('sympal', 'Clearing routing.yml...');

    $code = file_get_contents($path.'/lib/myUser.class.php');
    file_put_contents($path.'/lib/myUser.class.php', str_replace('class myUser extends sfBasicSecurityUser',  'class myUser extends sfSympalUser', $code));
    $this->logSection('sympal', 'Modifying myUser to extends sfSympalUser...');
  }
}