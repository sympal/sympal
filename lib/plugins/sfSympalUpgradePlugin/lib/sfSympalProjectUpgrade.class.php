<?php

/**
 * Upgrades your project to be compatible from one version of sympal to another
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  upgrade
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
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

  /**
   * Returns an array of all possible upgrade files as an array with
   * the upgrade's key and version
   * 
   * This first looks for all .php files in the versions directory.
   * The files should have the format of VERSION_NUMBER (e.g. 1.0.0__1).
   * The project is then checked to see if 
   */
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
        if (strpos($info['filename'], '__') === false)
        {
          throw new sfException(sprintf('Invalid upgrade filename format for file "%s" - must contain a double underscore - VERSION__NUMBER (e.g. 1.0.0__1) ', $info['filename']));
        }
        
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
      if (!class_exists($upgradeClass))
      {
        throw new sfException(sprintf('Cannot find upgrade class for upgrade "%s" "#%s" - expecting "%s"', $upgrade['version'], $upgrade['number'], $upgradeClass));
      }
      
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