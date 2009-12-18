<?php

/**
 * sympal_content_types module configuration.
 *
 * @package    sympal
 * @subpackage sympal_content_types
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_content_typesGeneratorConfiguration extends BaseSympal_content_typesGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return array('site_id' => sfSympalContext::getInstance()->getSite()->getId());
  }
}
