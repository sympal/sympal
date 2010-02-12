<?php

class sfSympalPluginInstallTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The name of the functionality. i.e. sfSympal#NAME#Plugin'),
    ));

    $this->addOptions(array(
      new sfCommandOption('content-type', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of the content type to create', null),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'plugin-install';
    $this->briefDescription = 'Install a sympal plugin to an existing sympal installation';

    $this->detailedDescription = <<<EOF
The [sympal:plugin-install|INFO] is a task to install a plugin to an existing sympal installation.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array(sprintf('This command will install the plugin "%s"', $arguments['name']), 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    $this->createContext($this->configuration);
    $pluginManager = sfSympalPluginManager::getActionInstance($arguments['name'], 'install', $this->configuration, $this->formatter);
    $pluginManager->install();
  }
}