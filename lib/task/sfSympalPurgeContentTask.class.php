<?php

class sfSympalPurgeContentTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('content-type', sfCommandArgument::REQUIRED, 'The model name to purge the data into.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', sfSympalToolkit::getDefaultApplication()),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'purge-content';
    $this->briefDescription = 'Purge the content for the specified content type.';

    $this->detailedDescription = <<<EOF
The [sympal:purge-content|INFO] task purges the content for the specified type.

  [./symfony sympal:purge-content Page|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $contentType = Doctrine_Core::getTable('ContentType')->findOneByName($arguments['content-type']);

    if (!$contentType)
    {
      throw new InvalidArgumentException('Invalid content-type specified...');
    }

    Doctrine_Core::getTable('Content')
      ->createQuery('c')
      ->where('c.content_type_id = ?', $contentType->id)
      ->delete()
      ->execute();
  }
}