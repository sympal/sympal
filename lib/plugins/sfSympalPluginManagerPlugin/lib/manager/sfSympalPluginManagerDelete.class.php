<?php

class sfSympalPluginManagerDelete extends sfSympalPluginManagerUninstall
{
  public function delete()
  {
    return self::uninstall(true);
  }
}