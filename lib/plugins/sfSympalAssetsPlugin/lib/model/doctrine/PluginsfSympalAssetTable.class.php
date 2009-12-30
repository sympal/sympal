<?php
/**
 */
class PluginsfSympalAssetTable extends sfSympalDoctrineTable
{
  public function findByPath($path)
  {
    return Doctrine_Core::getTable('sfSympalAsset')
      ->createQuery('a')
      ->from('sfSympalAsset a INDEXBY a.name')
      ->andWhere('a.path = ?', $path)
      ->execute();
  }
}