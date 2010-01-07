<?php

class sfSympalDoctrineRecordI18nFilter extends sfDoctrineRecordI18nFilter
{
  public function filterGet(Doctrine_Record $record, $name)
  {
    $culture = sfDoctrineRecord::getDefaultCulture();
    if (isset($record['Translation'][$culture]) && $record['Translation'][$culture][$name])
    {
      return $record['Translation'][$culture][$name];
    }
    else
    {
      $defaultCulture = sfConfig::get('sf_default_culture');
      return $record['Translation'][$defaultCulture][$name];
    }
  }
}