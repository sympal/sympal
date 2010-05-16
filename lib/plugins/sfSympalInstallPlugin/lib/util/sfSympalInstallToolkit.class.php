<?php

/**
 * General toolkit class for installation
 * 
 * @package     sfSympalInstallPlugin
 * @subpackage  util
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfSympalInstallToolkit
{
  /**
   * Copies the given *.yml.sample from the given path into the given
   * destination directory
   * 
   * @param string $source The fill path to the .yml.sample file
   * @param string $destinationDir The full path to the dir into which to copy it
   * @param object Anything we can call ->logSection() on (usually sfTask)
   */
  public static function processSampleYamlFile($source, $destinationDir, $task = null)
  {
    if (!file_exists($destinationDir))
    {
      if ($task)
      {
        $task->logSection('fixtures', sprintf('Creating fixtures directory %s', $destinationDir));
      }
      mkdir($destinationDir, 0777, true);
    }
    
    // save it without the .sample
    $newFile = $destinationDir.'/'.str_replace('.sample', '', basename($source));

    if (file_exists($newFile))
    {
      if ($task)
      {
        $task->logSection('fixtures', 'Skipping file because it already exists '.$newFile);
      }
    }
    else
    {
      // execute the yaml file into a variable
      ob_start();
      include($source);
      $content = ob_get_clean();

      if ($task)
      {
        $task->logSection('fixtures', 'Created file '.$newFile);
      }
      file_put_contents($newFile, $content);
    }
  }
}