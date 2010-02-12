<?php

class sfSympalBuildSearchIndexTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'build-search-index';
    $this->briefDescription = 'Build the Sympal search index';

    $this->detailedDescription = <<<EOF
The [symfony sympal:build-search-index|INFO] task builds the search index.

  [./symfony sympal:build-search-index|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->createContext($this->configuration);

    $search = sfSympalSearch::getInstance();
    $models = sfSympalConfig::getSearchableModels();
    foreach ($models as $model)
    {
      $records = Doctrine_Core::getTable($model)->findAll();
      foreach ($records as $record)
      {
        $this->logSection('sympal', sprintf('Updating index for %s #%s (%s)', $model, $record->getId(), (string) $record));
        $search->updateSearchIndex($record);
      }
    }
  }
}