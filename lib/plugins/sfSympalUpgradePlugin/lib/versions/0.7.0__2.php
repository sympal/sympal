<?php

class sfSympalUpgrade0_7_0__2 extends sfSympalVersionUpgrade
{
  public function _doUpgrade()
  {
    $this->logSection('sympal', 'Migrating sfSympalUserPlugin to sfDoctrineGuardPlugin');

    $files = $this->_getFinder('file')->in($this->_getProjectClassDirectories());

    foreach ($files as $file)
    {
      $contents = file_get_contents($file);
      $changed = false;

      $contents = str_replace("::getTable('sfSympalUser')", "::getTable('sfGuardUser')", $contents, $count);
      $changed = $count || $changed;

      $contents = str_replace('::getTable("User")', "::getTable('sfGuardUser')", $contents, $count);
      $changed = $count || $changed;

      if ($changed)
      {
        $this->logSection('sympal', 'Migrating '.$file);
        file_put_contents($file, $contents);
      }
    }
  }
}