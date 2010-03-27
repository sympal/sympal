<?php

/**
 * Worker class to upgrade to sympal version 1.0.0 ALPHA 4
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  upgrade
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalUpgrade1_0_0_ALPHA4__1 extends sfSympalVersionUpgrade
{
  public function _doUpgrade()
  {
    $this->logSection('sympal', 'Changing base Table class from sfSympalDoctrineTable to Doctrine_Table');

    $files = $this->_getFinder('file')->name('*Table.class.php')->in($this->_getDoctrineModelDirectories());
    foreach ($files as $file)
    {
      $contents = file_get_contents($file);
      $changed = false;

      $contents = str_replace('extends sfSympalDoctrineTable', 'extends Doctrine_Table', $contents, $count);
      $changed = $count || $changed;

      if ($changed)
      {
        $this->logSection('sympal', '    Migrating '.$file);
        file_put_contents($file, $contents);
      }
    }
  }
}