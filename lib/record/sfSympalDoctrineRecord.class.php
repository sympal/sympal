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

  public function preDqlSelect($event)
  {
    if (!sfConfig::get('sf_cache') || (!sfSympalConfig::get('use_query_caching') && !sfSympalConfig::get('use_result_caching')))
    {
      return;
    }
    $record = $event->getInvoker();
    $query = $event->getQuery();

    if (sfSympalConfig::get('enable_query_caching'))
    {
      $query->useCache(true);
    }

    if (sfSympalConfig::get('enable_result_caching'))
    {
      $query->useResultCache(true);
    }
  }

  public function postInsert($event)
  {
    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir').'/sympal/*');
    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir').'/*/*/config/*');
    sfToolkit::clearGlob(sfConfig::get('sf_cache_dir').'/*/*/template/*');
  }

  public function postUpdate($event)
  {
    $this->postInsert($event);
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