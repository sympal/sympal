<?php
abstract class sfSympalDoctrineRecord extends sfDoctrineRecord
{
  public function getI18n($name)
  {
    if ($this->getTable()->hasRelation('Translation'))
    {
      $default = sfDoctrineRecord::getDefaultCulture();
      if ($this->Translation[$default][$name])
      {
        return $this->Translation[$default][$name];
      }

      $default = sfConfig::get('sf_default_culture');
      if ($this->Translation[$default][$name])
      {
        return $this->Translation[$default][$name];
      }

      foreach ($this->Translation as $key => $translation)
      {
        if ($translation[$name])
        {
          return $translation[$name];
        }
      }
    } else {
      return $this->_get($name);
    }
  }

  public function setI18n($name, $value)
  {
    if ($this->getTable()->hasRelation('Translation'))
    {
      $default = sfDoctrineRecord::getDefaultCulture();
      return $this->Translation[sfDoctrineRecord::getDefaultCulture()][$name] = $value;
    } else {
      return $this->_set($name, $value);
    }
  }
}