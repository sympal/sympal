<?php

/**
 * sympal_redirects module configuration.
 *
 * @package    sympal
 * @subpackage sympal_redirects
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class sympal_redirectsGeneratorConfiguration extends BaseSympal_redirectsGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return array('site_id' => sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId());
  }
}