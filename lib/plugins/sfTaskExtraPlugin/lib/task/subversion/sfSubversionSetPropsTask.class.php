<?php

require_once dirname(__FILE__).'/sfTaskExtraSubversionBaseTask.class.php';

/**
 * Subversion setup task.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfSubversionSetPropsTask.class.php 26597 2010-01-13 23:33:05Z Kris.Wallsmith $
 */
class sfSubversionSetPropsTask extends sfTaskExtraSubversionBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('with-svn', null, sfCommandOption::PARAMETER_REQUIRED, 'Subversion binary to use'),
    ));

    $this->namespace = 'subversion';
    $this->name = 'set-props';

    $this->briefDescription = 'Sets typical Subversion properties';

    $this->detailedDescription = <<<EOF
The [subversion:set-props|INFO] sets typical Subversion properties on your project
directories.

  [./symfony subversion:set-props|INFO]

This will set the [svn:ignore|COMMENT] property to [*|COMMENT] on the following directories:

  cache/
  data/sql
  lib/form/doctrine/base
  lib/form/doctrine/*/base
  lib/filter/doctrine/base
  lib/filter/doctrine/*/base
  lib/model/doctrine/base
  lib/model/doctrine/*/base
  lib/model/om
  lib/model/map
  log/
  web/uploads/

Additionally, the [svn:ignore|COMMENT] property will be set to [*_dev.php|COMMENT]
on the [web/|COMMENT] directory.

You can specify which svn binary to use with the [--with-svn|COMMENT] option:

  [./symfony subversion:set-props --with-svn=/path/to/svn|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $finder = sfFinder::type('dir')->name('base');

    $this->addIgnore(array_merge(
      $finder->in(array('lib/form/doctrine', 'lib/filter/doctrine', 'lib/model/doctrine')),
      array('cache', 'data/sql', 'lib/model/om', 'lib/model/map', 'log', 'web/uploads')
    ));

    $this->setSubversionProperty('svn:ignore', '*_dev.php', 'web');
  }
}
