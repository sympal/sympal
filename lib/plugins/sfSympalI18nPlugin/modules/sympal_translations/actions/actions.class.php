<?php

require_once dirname(__FILE__).'/../lib/sympal_translationsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_translationsGeneratorHelper.class.php';

/**
 * sympal_translations actions.
 *
 * @package    sympal
 * @subpackage sympal_translations
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_translationsActions extends autoSympal_translationsActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}
