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
class sfSympalUpgrade1_0_0_ALPHA4__3 extends sfSympalVersionUpgrade
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
      
      // default_theme
      if (isset($data['all']['sympal_config']['default_theme']))
      {
        $value = $data['all']['sympal_config']['default_theme'];
        unset ($data['all']['sympal_config']['default_theme']);
        $this->createThemeEntry($data);
        
        $data['all']['sympal_config']['theme']['default_theme'] = $value;
        $needsSaving = true;
      }

      // allow_changing_theme_by_url
      if (isset($data['all']['sympal_config']['allow_changing_theme_by_url']))
      {
        $value = $data['all']['sympal_config']['allow_changing_theme_by_url'];
        unset ($data['all']['sympal_config']['allow_changing_theme_by_url']);
        $this->createThemeEntry($data);
        
        $data['all']['sympal_config']['theme']['allow_changing_theme_by_url'] = $value;
        $needsSaving = true;
      }

      // theme_request_parameter_name
      if (isset($data['all']['sympal_config']['theme_request_parameter_name']))
      {
        $value = $data['all']['sympal_config']['theme_request_parameter_name'];
        unset ($data['all']['sympal_config']['theme_request_parameter_name']);
        $this->createThemeEntry($data);
        
        $data['all']['sympal_config']['theme']['theme_request_parameter_name'] = $value;
        $needsSaving = true;
      }
      
      if ($needsSaving)
      {
        $this->logSection('sympal', sprintf('Upgrading "%s"', $file));
        file_put_contents($file, sfYaml::dump($data, 4));
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