<?php

class sfSympalUpgradeTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('download-new', null, sfCommandOption::PARAMETER_NONE, 'Check if a new version exists on the web and download it first before running the upgrade tasks.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'upgrade';
    $this->briefDescription = 'Upgrade a Sympal project by running any new upgrade tasks.';

    $this->detailedDescription = <<<EOF
The [symfony sympal:upgrade|INFO] task upgrades a Sympal project by running any new upgrade tasks.

  [./symfony sympal:upgrade|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    if (isset($options['download-new']) && $options['download-new'])
    {
      $upgrade = new sfSympalUpgradeFromWeb($this->configuration, $this->dispatcher, $this->formatter);
      if ($upgrade->hasNewVersion())
      {
        if (
          !$options['no-confirmation']
          &&
          !$this->askConfirmation(array_merge(
            array(sprintf('A new version of Sympal was detected! Would you like to download and upgrade to %s?', $upgrade->getLatestVersion()), ''),
            array('Are you sure you want to proceed? (y/N)')
          ), 'QUESTION_LARGE', false)
        )
        {
          $this->logSection('sympal', 'task aborted');

          return 1;
        }

        $upgrade->download();
      } else {
        throw new sfException('No new version of Sympal was found!');
      }
    } else {
      $upgrade = new sfSympalProjectUpgrade($this->configuration, $this->dispatcher, $this->formatter);      
    }

    $this->logSection('sympal', 'Checking for upgrade tasks to execute...');

    if ($upgrade->hasUpgrades())
    {
      $upgrades = $upgrade->getUpgrades();
      $numUpgrades = count($upgrades);
      $textUpgrades = array();
      foreach ($upgrades as $u)
      {
        $textUpgrades[] = $u['version'].' upgrade #'.$u['number'];
      }
      if (
        !$options['no-confirmation']
        &&
        !$this->askConfirmation(array_merge(
          array($numUpgrades .' upgrade '.($numUpgrades > 1 ? 'tasks' : 'task').' found! The following upgrades will be executed:', ''),
          array_map(create_function('$v', 'return \' - \'.$v;'), $textUpgrades),
          array('', 'Are you sure you want to proceed? (y/N)')
        ), 'QUESTION_LARGE', false)
      )
      {
        $this->logSection('doctrine', 'task aborted');
    
        return 1;
      }
      $upgrade->upgrade();

      $this->logSection('sympal', 'Successfully executed upgrade tasks');
      $this->logSection('sympal', 'You have successfully upgraded Sympal');
    } else if (isset($options['download-new']) && $options['download-new']) {
      $this->logSection('sympal', 'No upgrade tasks required for upgrade');
    } else { 
      throw new sfException('Nothing to upgrade.');
    }
  }
}