<?php

/**
 * Class for shortcuts, global helper methods, etc.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympal
{
  public static function getRequest()
  {
    return sfContext::getInstance()->getRequest();
  }

  public static function getResponse()
  {
    return sfContext::getInstance()->getResponse();
  }

  public static function getUser()
  {
    return sfContext::getInstance()->getUser();
  }

  public static function getGuardUser()
  {
    return sfContext::getInstance()->getGuardUser();
  }

  public static function getContext()
  {
    return sfContext::getInstance();
  }

  public static function getSympalContext()
  {
    return sfSympalContext::getInstance();
  }

  public static function getSymfonyContext()
  {
    return sfContext::getInstance();
  }

  public static function getSympalConfiguration()
  {
    return sfSympalConfiguration::getActive();
  }

  public static function getSymfonyConfiguration()
  {
    return sfProjectConfiguration::getActive();
  }

  public static function getEventDispatcher()
  {
    return sfProjectConfiguration::getActive()->getEventDispatcher();
  }
}