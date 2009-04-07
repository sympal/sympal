<?php

class sfSympalPluginInfo
{
  protected
    $_readme,
    $_packageXml;

  public function __construct($name)
  {
    $this->_name = $name;
    $this->_initialize();
  }
  
  protected function _initialize()
  {
    if ($pluginConfiguration = sfSympalPluginToolkit::isPluginDownloaded($this->_name))
    {
      $downloadPath = $pluginConfiguration->getRootDir();
    } else {
      $downloadPath = sfSympalPluginToolkit::getPluginDownloadPath($this->_name);
    }

    $packageXmlPath = $downloadPath.'/package.xml';
    $readmePath = $downloadPath.'/README';

    if (@file_get_contents($packageXmlPath))
    {
      $this->_packageXml = simplexml_load_file($packageXmlPath);
    }

    if ($readme = @file_get_contents($readmePath))
    {
      $this->_readme = $readme;
    }
  }

  public function hasReadme()
  {
    return !empty($this->_readme);
  }

  public function getReadme()
  {
    return sfSympalMarkdownRenderer::convertToHtml($this->_readme);
  }

  public function __call($method, $arguments)
  {
    if (substr($method, 0, 3) == 'get')
    {
      $what = substr($method, 3, strlen($method));
      $what = sfInflector::tableize($what);

      if (isset($this->_packageXml->$what))
      {
        $value = $this->_packageXml->$what;
        if (!empty($arguments))
        {
          foreach ($arguments as $name)
          {
            $value = $value->$name;
          }
        }
        $value = current($value);
        return $value;
      } else {
        return 'Blog';
      }
    }
  }
}