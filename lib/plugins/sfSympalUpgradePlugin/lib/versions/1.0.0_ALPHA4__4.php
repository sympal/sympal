<?php

/**
 * Worker class to upgrade to sympal version 1.0.0 ALPHA 4
 * 
 * This changes all of the content type configs (e.g. "page") to
 * their model name (e.g. sfSympalPage)
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  upgrade
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalUpgrade1_0_0_ALPHA4__4 extends sfSympalVersionUpgrade
{
  public function _doUpgrade()
  {
    $this->logSection('sympal', 'Moving some config values');
    
    $finder = sfFinder::type('file')->name('app.yml');
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
        
        if (isset($value['content_templates']))
        {
          $slug = $key;
          
          $contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneBySlug($slug);
          if (!$contentType)
          {
            // See if it was already upgraded. If it matches the name already, we're good
            $contentType = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($slug);
            if (!$contentType)
            {
              $this->logSection('sympal', sprintf('Attempting to change key app_sympal_config_%s', $slug), null, 'ERROR');
              $this->logSection('sympal', sprintf('In file "%s"', $file), null, 'ERROR');
              $this->logSection('sympal', sprintf('The %s slug should be changed to the name of the content type.', $slug), null, 'ERROR');
              $this->logSection('sympal', sprintf('However, the content type record with slug %s could not be found.', $slug), null, 'ERROR');
              $this->logSection('sympal', 'Skipping content type');
            }
            
            continue;
          }
          
          $needsSaving = true;
          $data['all']['sympal_config'][$contentType->name] = $value;
          unset($data['all']['sympal_config'][$key]);
        }
      }
      
      if ($needsSaving)
      {
        $this->logSection('sympal', sprintf('Upgrading "%s"', $file));
        file_put_contents($file, sfYaml::dump($data, 6));
      }
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