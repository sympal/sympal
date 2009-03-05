<?php

require_once dirname(__FILE__).'/../lib/sympal_languagesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_languagesGeneratorHelper.class.php';

/**
 * sympal_languages actions.
 *
 * @package    sympal
 * @subpackage sympal_languages
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_languagesActions extends autoSympal_languagesActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}
