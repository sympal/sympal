<?php

class sfSympalSharedPropertiesFilter extends Doctrine_Record_Filter
{
  public function init()
  {
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    $contentTypes = sfSympalToolkit::getContentTypesCache();
    foreach ($contentTypes as $contentType)
    {
      try {
        $record->$contentType->$name = $value;
        return $record;
      } catch (Exception $e) {
        $record->$contentType->free();
      }
    }
    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    $contentTypes = sfSympalToolkit::getContentTypesCache();
    foreach ($contentTypes as $contentType)
    {
      try {
        return $record->$contentType->$name;
      } catch (Exception $e) {
        $record->$contentType->free();
      }
    }
    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}