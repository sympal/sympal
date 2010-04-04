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
class sfSympalUpgrade1_0_0_ALPHA4__2 extends sfSympalVersionUpgrade
{
  protected $_dirs = array(
    'lib/model/doctrine/sfSympalPlugin' => 'lib/model/doctrine/sfSympalCMFPlugin',
    'lib/form/doctrine/sfSympalPlugin' => 'lib/form/doctrine/sfSympalCMFPlugin',
    'lib/filter/doctrine/sfSympalPlugin' => 'lib/filter/doctrine/sfSympalCMFPlugin',
  );

  public function _doUpgrade()
  {
    $this->logSection('sympal', 'Moving model/form/filter files from lib/*/doctrine/sfSympalPlugin to lib/*/doctrine/sfSympalCMFPlugin');
    
    $finder = sfFinder::type('file');
    $filesystem = new sfFilesystem($this->_dispatcher, $this->_formatter);
    
    foreach ($this->_dirs as $origin => $destination)
    {
      $this->logSection('sympal', sprintf('Mirroring %s to %s', $origin, $destination));
      $filesystem->mirror(
        sfConfig::get('sf_root_dir').'/'.$origin,
        sfConfig::get('sf_root_dir').'/'.$destination,
        $finder
      );
      
      $this->logSection('sympal', sprintf('Deleting %s', $origin));
      
      // remove the files first
      foreach ($finder->in(sfConfig::get('sf_root_dir').'/'.$origin) as $file)
      {
        $filesystem->remove(sfConfig::get('sf_root_dir').'/'.$origin.'/'.$file);
      }
      // remove the dirs
      $filesystem->remove(sfConfig::get('sf_root_dir').'/'.$origin.'/base');
      $filesystem->remove(sfConfig::get('sf_root_dir').'/'.$origin);
    }
  }
}