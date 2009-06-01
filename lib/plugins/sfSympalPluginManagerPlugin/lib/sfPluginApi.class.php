<?php

class sfPluginApi
{
  protected
    $_url = 'http://www.symfony-project.org/plugins/api',
    $_version = '1.0',
    $_username,
    $_password,
    $_cacheDir;

  public function __construct($username, $password, $cacheDir = null)
  {
    $this->_username = $username;
    $this->_password = $password;
    if (is_null($cacheDir))
    {
      $cacheDir = dirname(__FILE__).'/sf_plugins_cache';
    }
    if (!is_dir($cacheDir))
    {
      mkdir($cacheDir, 0777, true);
    }
    $this->_cacheDir = $cacheDir;
  }

  public function getUsername()
  {
    return $this->_username;
  }

  public function getPassword()
  {
    return $this->_password;
  }

  public function getApiUrl()
  {
    return $this->_url;
  }

  public function getVersion()
  {
    return $this->_version;
  }

  public function setUsername($username)
  {
    $this->_username = $username;
  }

  public function setPassword($password)
  {
    $this->_password = $password;
  }

  public function setApiUrl($url)
  {
    $this->_url = $url;
  }

  public function setVersion($version)
  {
    $this->_version = $version;
  }

  public function get($path, $params = array())
  {
    return $this->_call($path, $params, __FUNCTION__);
  }

  public function put($path, $params = array())
  {
    return $this->_call($path, $params, __FUNCTION__);
  }

  public function delete($path, $params = array())
  {
    return $this->_call($path, $params, __FUNCTION__);
  }

  public function post($path, $params = array())
  {
    return $this->_call($path, $params, __FUNCTION__);
  }

  protected function _call($path, $params = array(), $method = 'GET')
  {
    $url = $this->_url.'/'.$this->_version.'/'.$path;

    $method = strtoupper($method);
    $auth = $this->_username.':'.$this->_password;

    $key = md5($url.$auth.$method);

    $cachePath = $this->_cacheDir.'/'.$key.'.cache';
    if (!file_exists($cachePath) || $method != 'GET')
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
      curl_setopt($ch, CURLOPT_USERPWD, $auth);

      $result = curl_exec($ch);

      file_put_contents($cachePath, $result);

      curl_close($ch);
    } else {
      $result = file_get_contents($cachePath);
    }
    $xml = simplexml_load_string($result);

    return self::simpleXmlToArray($xml);
  }

  public static function simpleXmlToArray($xml)
  {
    if ($xml instanceof SimpleXMLElement)
    {
      $x = $xml;
      $xml = get_object_vars($xml);
    }

    if (is_array($xml))
    {
      if (count($xml) == 0)
      {
        return (string) $x;
      }

      $r = array();
      foreach($xml as $k => $v)
      {
        $r[$k] = self::simpleXmlToArray($v);
      }

      if (isset($r['@attributes']))
      {
        foreach ($r['@attributes'] as $k => $v)
        {
          $r[$k] = $v;
        }
        unset($r['@attributes']);
      }

      return $r;
    }
  
    return (string) $xml;
  }
}