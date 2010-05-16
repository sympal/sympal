<?php

/**
 * Represents a site.
 */
abstract class PluginsfSympalSite extends BasesfSympalSite
{
  public function deleteSiteAndApplication()
  {
    $this->delete();
    $this->deleteApplication();
  }

  /**
   * Delete application associated with site.
   *
   * @return null
   */
  public function deleteApplication()
  {
    sfToolkit::clearDirectory(sfConfig::get('sf_apps_dir').'/'.$this->slug);
    rmdir(sfConfig::get('sf_apps_dir').'/'.$this->slug);
    unlink(sfConfig::get('sf_web_dir').'/'.$this->slug.'_dev.php');
    unlink(sfConfig::get('sf_web_dir').'/'.$this->slug.'.php');
  }
}