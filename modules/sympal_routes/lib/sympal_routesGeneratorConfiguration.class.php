<?php

/**
 * sympal_routes module configuration.
 *
 * @package    sensiolabsus
 * @subpackage sympal_routes
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z fabien $
 */
class sympal_routesGeneratorConfiguration extends BaseSympal_routesGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return array('site_id' => sfSympalContext::getInstance()->getSiteRecord()->getId());
  }
}