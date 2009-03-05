<?php

require_once dirname(__FILE__).'/../lib/sympal_sitesGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_sitesGeneratorHelper.class.php';

/**
 * sympal_sites actions.
 *
 * @package    sympal
 * @subpackage sympal_sites
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_sitesActions extends autosympal_sitesActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}