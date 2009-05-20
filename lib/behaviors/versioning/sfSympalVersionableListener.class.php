<?php

class sfSympalVersionableListener extends Doctrine_Record_Listener
{
  public function preValidate(Doctrine_Event $event)
  {
    $record = $event->getInvoker();
    if ($record->isModified())
    {
      $changes = $this->buildChangesArray($record);
      if (!empty($changes))
      {
        $record->previous_version = $record->version ? $record->version:0;
        $record->version = $this->getNextVersion($record);
        $record->mapValue('sympal_versionable_changes', $changes);
      }
    }
  }

  public function postUpdate(Doctrine_Event $event)
  {
    $record = $event->getInvoker();
    if (isset($record->sympal_versionable_changes))
    {
      $record->refresh();

      $changes = $record->sympal_versionable_changes;
      unset($record->sympal_versionable_changes);

      $table = $record->getTable();
      foreach ($changes as $name => $value)
      {
        $value['new_value'] = (string) $record->$name;
        $changes[$name] = $value;
        $name = $table->getFieldName($name);
        $type = $table->getTypeOf($name);
        if (!$record->isValueModified($type, $value['old_value'], $value['new_value']))
        {
          unset($changes[$name]);
        }
      }

      if (!empty($changes))
      {
        $version = new Version();
        $version->record_id = $record->id;
        $version->record_type = get_class($record);
        $version->changes_array = $changes;
        $version->version = $record->version;

        if (sfContext::hasInstance())
        {
          $user = sfContext::getInstance()->getUser()->getSympalUser();
        } else {
          $user = Doctrine::getTable('User')->findOneByIsSuperAdmin(true);
        }

        $version->CreatedBy = $user;
        $version->save();
      }
    }
  }

  public function postInsert(Doctrine_Event $event)
  {
    return $this->postUpdate($event);
  }

  public function buildChangesArray(Doctrine_Record $record)
  {
    $newValues = $record->getNewValues();
    $oldValues = $record->getOldValues();
    $record->resetVersioningValues();

    $fields = sfSympalConfig::getVersionedModelOptions(get_class($record));
    $changes = array();
    foreach ($fields as $field)
    {
      if (!isset($newValues[$field]))
      {
        continue;
      }

      $newValue = $newValues[$field];
      $oldValue = isset($oldValues[$field]) ? $oldValues[$field]:null;
      if ((string) $oldValue != (string) $newValue)
      {
        $changes[$field] = array(
          'old_value' => $oldValue,
          'new_value' => $newValue
        );
      }
    }

    return $changes;
  }

  public function getNextVersion(Doctrine_Record $record)
  {
    if ($record->exists())
    {
        $recordType = get_class($record);

        $q = Doctrine_Query::create()
          ->select('MAX(v.version) AS max_version')
          ->from('Version v')
          ->andWhere('v.record_type = ?', $recordType)
          ->andWhere('v.record_id = ?', $record->id);

        $result = $q->execute(array(), Doctrine::HYDRATE_ARRAY);

        return isset($result[0]['max_version']) ? ($result[0]['max_version'] + 1):1;
    } else {
        return 1;
    }
  }
}