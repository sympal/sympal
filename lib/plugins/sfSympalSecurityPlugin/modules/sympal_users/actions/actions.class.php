<?php

require_once dirname(__FILE__).'/../lib/sympal_usersGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/sympal_usersGeneratorHelper.class.php';

/**
 * sympal_users actions.
 *
 * @package    sympal
 * @subpackage sympal_users
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z jwage $
 */
class sympal_usersActions extends autosympal_usersActions
{
  public function preExecute()
  {
    parent::preExecute();
    sfSympalTools::changeLayout(sfSympalConfig::get('default_layout'));
  }
}