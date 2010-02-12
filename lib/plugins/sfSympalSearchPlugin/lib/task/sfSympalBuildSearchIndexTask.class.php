<?php

class sfSympalBuildSearchIndexTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('all', null, sfCommandOption::PARAMETER_NONE, 'Index all applications'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'build-search-index';
    $this->briefDescription = 'Build the Sympal search index';

    $this->detailedDescription = <<<EOF
The [symfony sympal:build-search-index|INFO] task builds the search index for the first Sympal site found in the applications directory.

  [./symfony sympal:build-search-index|INFO]

You can optionally specify an option to build all sites:

  [./symfony sympal:build-search-index --all|INFO]

Or you can build the index for a specific site:

  [./symfony sympal:build-search-index --application=another_site|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->createContext($this->configuration);

    if ($this->configuration instanceof sfApplicationConfiguration && !$options['all'])
    {
      $this->sites = Doctrine_Core::getTable('sfSympalSite')
        ->createQuery('s')
        ->where('s.slug = ?', sfConfig::get('sf_app'))
        ->execute();
    } else {
      $this->sites = Doctrine_Core::getTable('sfSympalSite')
        ->createQuery('s')
        ->execute();
    }

    foreach ($this->sites as $site)
    {
      if (!$this->configuration instanceof sfApplicationConfiguration || $options['all'])
      {
        $this->configuration = $this->createConfiguration($site->slug, $options['env']);
        $this->createContext($this->configuration);
      }

      $this->logSection('sympal', sprintf('Indexing models for site "%s"', sfConfig::get('sf_app')));
      $search = sfSympalSearch::getInstance();
      $models = sfSympalConfig::getSearchableModels();
      foreach ($models as $model)
      {
        $records = Doctrine_Core::getTable($model)->findAll();
        $this->logBlock(sprintf('Indexing "%s"', $model), 'INFO');
        foreach ($records as $record)
        {
          $this->logBlock(sprintf('...%s (%s)', $record->getId(), (string) $record), 'COMMENT');
          $search->updateSearchIndex($record);
        }
      }
    }
  }
}