<?php
class sfSympalBlogPluginConfiguration extends sfPluginConfiguration
{
  public static
    $dependencies = array(
      'sfSympalPlugin',
      'sfSympalUserPlugin'
    );
}