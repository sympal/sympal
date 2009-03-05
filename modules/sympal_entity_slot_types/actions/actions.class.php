<?php

require_once dirname(__FILE__).'/../lib/sympal_entity_slot_typesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_entity_slot_typesGeneratorHelper.class.php';

/**
 * sympal_entity_slot_types actions.
 *
 * @package    sympal
 * @subpackage sympal_entity_slot_types
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_entity_slot_typesActions extends autoSympal_entity_slot_typesActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}
