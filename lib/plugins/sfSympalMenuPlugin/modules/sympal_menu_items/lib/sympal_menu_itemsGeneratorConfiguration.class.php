<?php

/**
 * sympal_menu_items module configuration.
 *
 * @package    sympal
 * @subpackage sympal_menu_items
 * @author     Your name here
 * @version    SVN: $Id: configuration.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_menu_itemsGeneratorConfiguration extends BaseSympal_menu_itemsGeneratorConfiguration
{
  public function getFilterDefaults()
  {
    return array('site_id' => sfSympalContext::getInstance()->getSiteRecord()->getId());
  }
}
