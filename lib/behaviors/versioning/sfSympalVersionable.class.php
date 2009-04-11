<?php

class sfSympalVersionable extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->hasColumn('version', 'integer', null, array('default' => 0));
    $this->hasColumn('previous_version', 'integer', null, array('default' => 0));
    $this->_table->addRecordListener(new sfSympalVersionableListener());
  }

  public function revert($version)
  {
    $record = $this->getInvoker();
    $changes = $this->getVersionRevertArray($version);

    $record->fromArray($changes);
    $record->save();

    return true;
  }

  public function undo()
  {
    $record = $this->getInvoker();
    $prevVersion = $record->previous_version;
    $version = $prevVersion > 0 ? $prevVersion:$record->version;
    return $this->revert($version);
  }

  public function getVersionRevertArray($version)
  {
    return $this->getVersionChanges($version, true);
  }

  public function getVersions()
  {
    $record = $this->getInvoker();
    $recordType = get_class($record);

    $q = Doctrine::getTable('Version')
      ->createQuery('v')
      ->innerJoin('v.Changes c')
      ->andWhere('v.record_type = ?', $recordType)
      ->andWhere('v.record_id = ?', $record->id);

    return $q->execute();
  }

  public function getVersionChanges($version, $revert = false)
  {
    $record = $this->getInvoker();
    $recordType = get_class($record);

    $q = Doctrine::getTable('Version')
      ->createQuery('v')
      ->innerJoin('v.Changes c')
      ->andWhere('v.record_type = ?', $recordType)
      ->andWhere('v.record_id = ?', $record->id)
      ->andWhere('v.version = ?', $version);

    $version = $q->fetchOne();

    if ($version)
    {
      if ($revert)
      {
        return $version->getRevertArray();
      } else {
        return $version->getChangesArray();
      }
    } else {
      throw new sfException('Could not find version #'.$version.' for the '.$recordType.' with an id of '.$record->id);
    }
  }
}