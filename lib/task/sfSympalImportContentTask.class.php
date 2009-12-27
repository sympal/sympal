<?php

class sfSympalImportContentTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('database', sfCommandArgument::REQUIRED, 'The database to import content from.'),
      new sfCommandArgument('table', sfCommandArgument::REQUIRED, 'The table in the database to import content from.'),
      new sfCommandArgument('model', sfCommandArgument::REQUIRED, 'The model name to import the data into.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'import-content';
    $this->briefDescription = 'Import Sympal content from a table in a database';

    $this->detailedDescription = <<<EOF
The [sympal:import-content|INFO] task reports some statistics back to Symfony.
Like what plugins you are using, versions, etc.

  [./symfony sympal:import-content my_database the_table MyContentType|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $database = $databaseManager->getDatabase($arguments['database']);
    $conn = $database->getDoctrineConnection();
    $data = $conn->fetchAll('SELECT * FROM '.$arguments['table']);
    $model = $arguments['model'];
    $modelTable = Doctrine_Core::getTable($model);
    $isContentType = $modelTable->hasTemplate('sfSympalContentType');

    foreach ($data as $row)
    {
      if ($isContentType)
      {
        $record = sfSympalContent::createNew($model);
        $record->CreatedBy = Doctrine_Core::getTable('sfGuardUser')->findOneByIsSuperAdmin(true);
        $record->getRecord()->fromArray($row);
        $record->date_published = new Doctrine_Expression('NOW()');
        $record->slug = (string) $record;
      } else {
        $record = new $model();
        $record->fromArray($row);
      }
      $record->save();
    }
  }
}