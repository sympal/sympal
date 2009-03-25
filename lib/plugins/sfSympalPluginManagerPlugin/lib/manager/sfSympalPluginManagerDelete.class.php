<?php

class sfSympalPluginManagerDelete extends sfSympalPluginManagerUninstall
{
  public function delete($name, $contentTypeName = null)
  {
    return self::uninstall($name, $contentTypeName, true);
  }
}