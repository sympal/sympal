<?php

require_once dirname(__FILE__).'/sfTaskExtraGeneratorBaseTask.class.php';

/**
 * Generates a single unit test stub script
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfGenerateTestTask.class.php 26469 2010-01-11 06:53:59Z Kris.Wallsmith $
 */
class sfGenerateTestTask extends sfTaskExtraGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('class', sfCommandArgument::REQUIRED, 'The class to test'),
    ));

    $this->addOptions(array(
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_NONE, 'Overwrite any existing test file'),
      new sfCommandOption('editor-cmd', null, sfCommandOption::PARAMETER_REQUIRED, 'Open script with this command upon creation'),
    ));

    $this->namespace = 'generate';
    $this->name = 'test';

    $this->briefDescription = 'Generates a single unit test stub script';

    $this->detailedDescription = <<<EOF
The [generate:test|INFO] task generates an empty unit test script in your
[test/unit/|COMMENT] directory and reflects the organization of your [lib/|COMMENT] directory:

  [./symfony generate:test myClass|INFO]

To open the test script in your test editor once the task completes, use the
[--editor-cmd|COMMENT] option:

  [./symfony generate:test myClass --editor-cmd=mate|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (!class_exists($arguments['class']))
    {
      throw new InvalidArgumentException(sprintf('The class "%s" does not exist.', $arguments['class']));
    }

    // base lib and test directories
    $r = new ReflectionClass($arguments['class']);
    list($libDir, $testDir) = $this->getDirectories($r->getFilename());
    $path = str_replace($libDir, '', dirname($r->getFilename()));
    $test = $testDir.'/unit'.$path.'/'.$r->getName().'Test.php';

    // use either the test directory or project's bootstrap
    if (!file_exists($bootstrap = $testDir.'/bootstrap/unit.php'))
    {
      $bootstrap = sfConfig::get('sf_test_dir').'/bootstrap/unit.php';
    }

    if (file_exists($test) && $options['force'])
    {
      $this->getFilesystem()->remove($test);
    }

    if (file_exists($test))
    {
      $this->logSection('task', sprintf('A test script for the class "%s" already exists.', $r->getName()), null, 'ERROR');
    }
    else
    {
      $this->getFilesystem()->copy(dirname(__FILE__).'/skeleton/test/UnitTest.php', $test);
      $this->getFilesystem()->replaceTokens($test, '##', '##', array(
        'CLASS'     => $r->getName(),
        'BOOTSTRAP' => $this->getBootstrapPathPhp($bootstrap, $test),
        'DATABASE'  => $this->isDatabaseClass($r) ? "\n\$databaseManager = new sfDatabaseManager(\$configuration);\n" : '',
      ));
    }

    if (isset($options['editor-cmd']))
    {
      $this->getFilesystem()->execute($options['editor-cmd'].' '.escapeshellarg($test));
    }
  }

  /**
   * Returns reference of the bootstrap file from the test file.
   * 
   * @param string $bootstrapFile
   * @param string $testFile
   * 
   * @return string PHP code for referencing the bootstrap file from the test file
   */
  protected function getBootstrapPathPhp($bootstrapFile, $testFile)
  {
    if (0 === strpos($testFile, $path = realpath(dirname($bootstrapFile).'/..')))
    {
      $path = str_repeat('/..', substr_count(dirname(str_replace($path, '', $testFile)), DIRECTORY_SEPARATOR));
    }
    else if (0 === strpos($bootstrapFile, sfConfig::get('sf_test_dir')) && 0 === strpos($testFile, sfConfig::get('sf_root_dir')))
    {
      $path = str_repeat('/..', substr_count(dirname(str_replace(sfConfig::get('sf_root_dir'), '', $testFile)), DIRECTORY_SEPARATOR)).'/test';
    }
    else
    {
      throw new InvalidArgumentException(sprintf('A relative path from "%s" to "%s" could not be determined.', $testFile, $bootstrapFile));
    }

    return sprintf('dirname(__FILE__).\'%s/bootstrap/unit.php\'', $path);
  }

  /**
   * Returns paths the lib and test directory corresponding to the supplied file path.
   * 
   * @param string $path An absolute path
   * 
   * @return array The supplied path's lib and test directories
   * 
   * @throws InvalidArgumentException If the path is not in the project of any connected plugins' lib directories
   */
  protected function getDirectories($path)
  {
    if (0 === strpos($path, sfConfig::get('sf_lib_dir')))
    {
      return array(sfConfig::get('sf_lib_dir'), sfConfig::get('sf_test_dir'));
    }
    else
    {
      foreach ($this->configuration->getPluginSubPaths('/lib') as $pluginLibDir)
      {
        if (0 === strpos($path, $pluginLibDir))
        {
          // create the test directory before normalizing its path
          if (!file_exists($testDir = $pluginLibDir.'/../test'))
          {
            $this->getFilesystem()->mkdirs($testDir);
          }

          return array($pluginLibDir, realpath($testDir));
        }
      }
    }

    throw new InvalidArgumentException(sprintf('The file "%s" is not in the project or a connected plugin’s lib directory.', $path));
  }

  /**
   * Returns true if the supplied class uses the database.
   * 
   * @return boolean
   */
  protected function isDatabaseClass(ReflectionClass $r)
  {
    return
      // propel
      (class_exists('Propel') && ($r->isSubclassOf('BaseObject') || 'Peer' == substr($r->getName(), -4)))
      ||
      // doctrine
      (class_exists('Doctrine') && ($r->isSubclassOf('Doctrine_Record') || $r->isSubclassOf('Doctrine_Table')))
      ||
      // either
      $r->isSubclassOf('sfFormObject') || $r->isSubclassOf('sfFormFilter')
    ;
  }
}
