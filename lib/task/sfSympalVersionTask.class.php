<?php

class sfSympalVersionTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('check', null, sfCommandOption::PARAMETER_NONE, 'Check for new versions.'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'version';
    $this->briefDescription = 'Show the current Sympal version and check for new versions.';

    $this->detailedDescription = <<<EOF
The [symfony sympal:version|INFO] task outputs the current Sympal version.

  [./symfony sympal:version|INFO]

You can also check for new versions:

  [./symfony sympal:version --check]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $version = sfSympalConfig::getCurrentVersion();
    $formattedVersion = $this->formatter->format($version, 'INFO');
    $dir = $this->configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir();

    if (isset($options['check']) && $options['check'])
    {
      $upgrade = new sfSympalUpgradeFromWeb($this->configuration, $this->dispatcher, $this->formatter);
      if ($upgrade->hasNewVersion())
      {
        $formattedVersion = $this->formatter->format($upgrade->getLatestVersion(), 'INFO');
        $this->log(sprintf("\nSympal %s is available for download.\n", $formattedVersion));
        $command = $this->formatter->format('./symfony sympal:upgrade --download-new', 'COMMENT');
        $this->log(sprintf("Run %s to download the new version and upgrade.\n", $command));
      } else {
        $this->log(sprintf("\nYou are up to date with Sympal %s (%s)\n", $formattedVersion, $dir));
      }
    } else {
      $this->log(sprintf('Current Sympal version %s (%s)', $formattedVersion, $dir));
    }
  }
}