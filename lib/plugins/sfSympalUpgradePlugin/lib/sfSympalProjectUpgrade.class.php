<?php

class sfSympalProjectUpgrade extends sfSympalUpgrade
{
  protected
    $_upgrades;

  public function hasUpgrades()
  {
    return $this->getNumUpgrades() > 0 ? true : false;
  }

  public function getNumUpgrades()
  {
    return count($this->getUpgrades());
  }

  public function getUpgrades()
  {
    if (!$this->_upgrades)
    {
      $this->_upgrades = array();
      $versions = array();
      $dir = dirname(__FILE__).'/versions';
      $files = sfFinder::type('file')->name('*.php')->in($dir);
      foreach ($files as $file)
      {
        $info = pathinfo($file);
        $e = explode('__', $info['filename']);
        $version = $e[0];
        $number = $e[1];

        if ($this->_isVersionNew($info['filename']))
        {
          $versions[] = array(
            'version' => $version,
            'number' => $number
          );
          $this->_upgrades[] = $version.'__'.$number;
        }
      }
      natcasesort($this->_upgrades);

      foreach ($this->_upgrades as $key => $version)
      {
        $this->_upgrades[$key] = $versions[$key];
      }
    }

    return $this->_upgrades;
  }

  public function checkForUpgrades()
  {
    if (!$this->hasUpgrades())
    {
      throw new sfException(sprintf('No upgrades detected, you are currently up to date.'));
    }
  }

  protected function _doUpgrade()
  {
    $this->checkForUpgrades();
    $this->_runUpgrades();
  }

  protected function _runUpgrades()
  {
    $upgrades = $this->getUpgrades();
    foreach ($upgrades as $upgrade)
    {
      $this->logSection('sympal', sprintf('...executing %s upgrade #%s', $upgrade['version'], $upgrade['number']));

      $upgradeClass = 'sfSympalUpgrade'.str_replace('.', '_', $upgrade['version'].'__'.$upgrade['number']);
      $upgradeInstance = new $upgradeClass($this->_configuration, $this->_dispatcher, $this->_formatter);
      $upgradeInstance->setVersion($upgrade['version']);
      $upgradeInstance->setNumber($upgrade['number']);
      $upgradeInstance->upgrade();
    }
  }

  private function _isVersionNew($version)
  {
    $versionHistory = sfSympalConfig::get('upgrade_version_history', null, array());
    return ! in_array($version, $versionHistory) ? true : false;
  }
}