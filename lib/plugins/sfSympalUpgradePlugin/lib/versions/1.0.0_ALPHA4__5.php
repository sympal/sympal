<?php

/**
 * Worker class to upgrade to sympal version 1.0.0 ALPHA 4
 * 
 * This changes all of the content type configs (e.g. "sfSympalPage") to
 * live beneath a "content_types" group, instead of at the root level
 * of sympal_config
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  upgrade
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalUpgrade1_0_0_ALPHA4__5 extends sfSympalVersionUpgrade
{
  public function _doUpgrade()
  {
    $this->logSection('sympal', 'Moving content type config to a new group "content_types"');
    
    $finder = sfFinder::type('file')->name('app.yml')->follow_link();
    $modifiedFiles = 0;
    foreach ($finder->in(sfConfig::get('sf_root_dir')) as $file)
    {
      $data = sfYaml::load($file);
      $needsSaving = false;
      
      // see if it potentially has anything we care about
      if (!isset($data['all']) || !isset($data['all']['sympal_config']))
      {
        continue;
      }
      
      $sympalConfig = $data['all']['sympal_config'];
      
      foreach ($sympalConfig as $key => $value)
      {
        if (!is_array($value))
        {
          continue;
        }
        
        // each content_type config should have a content_templates key
        if (isset($value['content_templates']))
        {
          $this->logSection('update', sprintf('Updating "%s" content type in "%s"', $key, $file));
          $needsSaving = true;
          
          if (!isset($data['all']['sympal_config']['content_types']))
          {
            $data['all']['sympal_config']['content_types'] = array();
          }
          
          $data['all']['sympal_config']['content_types'][$key] = $value;
          unset($data['all']['sympal_config'][$key]);
        }
      }
      
      if ($needsSaving)
      {
        $modifiedFiles++;
        $this->logSection('sympal', sprintf('Upgrading "%s"', $file));
        file_put_contents($file, sfYaml::dump($data, 7));
      }
    }
    
    if ($modifiedFiles)
    {
      $this->logSection('sympal', sprintf('Updated %s files', $modifiedFiles));
    }
    else
    {
      $this->logSection('sympal', 'No files updated');
    }
  }

  /**
   * Ensures that there's a "theme" array key in the data
   */
  protected function createThemeEntry($data)
  {
    if (!isset($data['all']['sympal_config']['theme']))
    {
      $data['all']['sympal_config']['theme'] = array();
    }
    
    return $data;
  }
}