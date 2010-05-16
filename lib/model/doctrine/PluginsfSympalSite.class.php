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
    // application itself (apps/$app)
    $appsDir = sfConfig::get('sf_apps_dir') . DIRECTORY_SEPARATOR . $this->slug;
    sfToolkit::clearDirectory($appsDir);
    rmdir($appsDir);

    // public files (web/$app_*.php)
    $pubPref = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $this->slug;
    unlink($pubPref . '_dev.php');
    unlink($pubPref . '.php');

    // fixtures (data/fixtures/$app)
    $fixtDir = implode(DIRECTORY_SEPARATOR, array(sfConfig::get('sf_data_dir'), 'fixtures', $this->slug));
    sfToolkit::clearDirectory($fixtDir);
    rmdir($fixtDir);
  }
}