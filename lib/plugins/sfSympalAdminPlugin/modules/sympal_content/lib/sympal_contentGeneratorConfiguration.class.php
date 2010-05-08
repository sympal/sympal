<?php

/**
 * sympal_content module configuration.
 *
 * @package    sympal
 * @subpackage sympal_content
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_contentGeneratorConfiguration extends BaseSympal_contentGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return array(
      'site_id' => sfSympalContext::getInstance()->getService('site_manager')->getSite()->getId()
    );
  }
}
