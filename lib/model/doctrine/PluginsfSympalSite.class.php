<?php

/**
 * Represents a site.
 */
abstract class PluginsfSympalSite extends BasesfSympalSite
{
  /**
   * Delete site record and all associated content.
   *
   * @return boolean
   */
  public function delete(Doctrine_Connection $conn = null)
  {
    // we *need* to call sfSympalContent::delete() for each record
    $contentTable = Doctrine_Core::getTable('sfSympalContent');
    foreach($contentTable->findBySiteId($this->getId()) as $record)
    {
      $record->delete();
    }

    return parent::delete();
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