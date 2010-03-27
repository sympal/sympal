<?php
/**
 */
class PluginsfSympalAssetTable extends Doctrine_Table
{
  public function findByPath($path)
  {
    return Doctrine_Core::getTable('sfSympalAsset')
      ->createQuery('a')
      ->from('sfSympalAsset a')
      ->andWhere('a.path = ?', $path)
      ->execute();
  }
}