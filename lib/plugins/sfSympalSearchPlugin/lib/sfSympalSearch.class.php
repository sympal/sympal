<?php

/**
 * Base search managing class for sympal
 * 
 * @package     sfSympalSearchPlugin
 * @subpackage  search
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
class sfSympalSearch
{
  private static $_instance;

  private $_index;

  public static function getInstance()
  {
    if (!self::$_instance)
    {
      self::$_instance = new sfSympalSearch();
    }
    return self::$_instance;
  }

  public function getIndex()
  {
    if (!$this->_index)
    {
      $index = sfConfig::get('sf_data_dir').'/sympal_'.sfConfig::get('sf_environment').'_search.index';
      if (file_exists($index))
      {
        $this->_index = Zend_Search_Lucene::open($index);
      }
      else
      {
        $this->_index = Zend_Search_Lucene::create($index);
      }
    }
    return $this->_index;
  }

  private function _getRecordSearchPrimaryKey(Doctrine_Record $record)
  {
    return get_class($record).'_'.$record->getId();
  }

  public function updateSearchIndex(Doctrine_Record $record)
  {
    $index = $this->getIndex();

    // remove existing entries
    foreach ($index->find('pk:'.$this->_getRecordSearchPrimaryKey($record)) as $hit)
    {
      $index->delete($hit->id);
    }

    $doc = new Zend_Search_Lucene_Document();

    // store job primary key to identify it in the search results
    $doc->addField(Zend_Search_Lucene_Field::Keyword('pk', $this->_getRecordSearchPrimaryKey($record)));

    if (method_exists($record, 'getSearchData'))
    {
      $data = $record->getSearchData();
    } else {
      $data = $record->toArray(false);
    }
    foreach ($data as $key => $value)
    {
      $doc->addField(Zend_Search_Lucene_Field::UnStored($key, $value, 'utf-8'));
    }

    // add job to the index
    $index->addDocument($doc);
    $index->commit();
  }

  public function getDoctrineSearchQuery($model, $query)
  {
    $hits = $this->getIndex()->find((string) $query);

    $pks = array();
    foreach ($hits as $hit)
    {
      $pk = $hit->pk;
      $e = explode('_', $pk);
      $modelName = $e[0];
      $pk = $e[1];
      $pks[$modelName][] = $pk;
    }

    $ids = isset($pks[$model]) ? $pks[$model] : array();
    $q = Doctrine_Core::getTable($model)
      ->createSearchQuery('c')
      ->addSelect('*');

    if (empty($ids))
    {
      $q->andWhere('c.id < 0');
    } else {
      $col = 'c.id';
      $n = 1;
      $select = "(CASE $col";
      foreach ($ids as $id)
      {
        $id = (int) $id;
        $select .= " WHEN $id THEN $n";
        $n++;
      }
      $select .= " ELSE $n";
      $select .= " END) AS id_order";
      $q->addSelect($select);
      $q->addOrderBy('id_order ASC');
      $q->andWhereIn('c.id', $ids);
    }

    return $q;
  }

  public function search($model, $query)
  {
    $q = $this->getDoctrineSearchQuery($model, $query);
    if ($q)
    {
      return $q->execute();
    } else {
      return array();
    }
  }
}