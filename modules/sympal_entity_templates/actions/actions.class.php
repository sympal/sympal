<?php

require_once dirname(__FILE__).'/../lib/sympal_entity_templatesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_entity_templatesGeneratorHelper.class.php';

/**
 * sympal_entity_templates actions.
 *
 * @package    sympal
 * @subpackage sympal_entity_templates
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_entity_templatesActions extends autoSympal_entity_templatesActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}
