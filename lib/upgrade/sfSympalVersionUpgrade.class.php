<?php

abstract class sfSympalVersionUpgrade extends sfSympalUpgrade
{
  protected
    $_version,
    $_number;

  public function upgrade()
  {
    $result = parent::upgrade();

    $versionHistory = sfSympalConfig::get('upgrade_version_history', null, array());
    $versionHistory[] = $this->_version.'__'.$this->_number;

    sfSympalConfig::writeSetting('upgrade_version_history', $versionHistory);

    return $result;
  }

  public function setVersion($version)
  {
    $this->_version = $version;
  }

  public function setNumber($number)
  {
    $this->_number = $number;
  }
}