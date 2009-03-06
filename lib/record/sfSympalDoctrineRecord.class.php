<?php
abstract class sfSympalDoctrineRecord extends sfDoctrineRecord
{
  public function construct()
  {
    $table = $this->getTable();

    if ($table->hasRelation('Entity'))
    {
      $this->unshiftFilter(new sfSympalDoctrineRecordFilter());
    }

    parent::construct();
  }

  public function save(Doctrine_Connection $conn = null)
  {
    $table = $this->getTable();

    if ($table->hasRelation('Entity') && $this->isNew())
    {
      $class = get_class($this);
      $this->Entity->slug = Doctrine_Inflector::urlize((string) $this);
      foreach (Doctrine::getTable('EntityType')->getRepository() as $entityType)
      {
        if ($entityType->name == $class)
        {
          $this->Entity->Type = $entityType;
          break;
        }
      }
      $this->Entity->Type->setName($class);
      $this->Entity->Type->setLabel($class);

      $site = sfSympalTools::getCurrentSite();
      foreach (Doctrine::getTable('Site')->getRepository() as $siteObj)
      {
        if ($siteObj->slug == $site)
        {
          $this->Entity->Site = $siteObj;
          break;
        }
      }
      $this->Entity->Site->setTitle($site);
    }

    return parent::save($conn);
  }

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