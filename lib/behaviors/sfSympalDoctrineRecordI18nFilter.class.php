<?php

/**
 * Doctrine record filter for allowing a fallback culture for i18n.
 * If you call to get the value for the current culture and it does not exist it
 * will get the default culture instead of returning nothing.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalDoctrineRecordI18nFilter extends sfDoctrineRecordI18nFilter
{
  /**
   * Filter the Doctrine_Record::get() calls to see if we can get the value
   * for the current or default culture
   *
   * @param Doctrine_Record $record
   * @param string $name The name of the i18n property to get
   * @return mixed $return
   */
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