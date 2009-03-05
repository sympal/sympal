<?php
class sfSympalDoctrineCacheDriver extends Doctrine_Cache_Driver
{
  public function __construct($options = array())
  {
    parent::__construct($options);

    if (!is_dir($path = $this->getRootPath()))
    {
      mkdir($this->getRootPath(), 0777, true);
    }
  }

  public function getRootPath()
  {
    return sfConfig::get('sf_cache_dir').'/sympal/';
  }

  public function getPath($id)
  {
    return $this->getRootPath().'/'.$id;
  }

  public function fetch($id, $testCacheValidity = true) 
  {
      return $this->contains($id) ? file_get_contents($this->getPath($id)):false;
  }

  public function contains($id) 
  {
      return file_exists($this->getPath($id)) ? true : false;
  }

  public function save($id, $data, $lifeTime = false)
  {
      return (bool) file_put_contents($this->getPath($id), $data);//, $lifeTime
  }

  public function delete($id) 
  {
      return unlink($this->getPath($id));
  }
}