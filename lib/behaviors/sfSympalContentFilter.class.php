<?php

class sfSympalContentFilter extends Doctrine_Record_Filter
{
  protected $_i18nFilter;

  public function init()
  {
    $this->_i18nFilter = new sfDoctrineRecordI18nFilter();
    $this->_i18nFilter->setTable($this->getTable());
    $this->_i18nFilter->init();
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
    try {
      return $this->_i18nFilter->filterSet($record, $name, $value);
    } catch (Exception $e) {}

    try {
      if ($record->getRecord())
      {
        $record->getRecord()->$name = $value;
        return $record;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
    try {
      return $this->_i18nFilter->filterGet($record, $name);
    } catch (Exception $e) {}

    try {
      if ($record->getRecord())
      {
        return $record->getRecord()->$name;
      }
    } catch (Exception $e) {}

    throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}