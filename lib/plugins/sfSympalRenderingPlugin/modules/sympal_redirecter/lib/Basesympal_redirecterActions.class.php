<?php

/**
 * Base actions for the sfSympalRenderingPlugin sympal_redirecter module.
 * 
 * @package     sfSympalRenderingPlugin
 * @subpackage  sympal_redirecter
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_redirecterActions extends sfActions
{
  public function executeIndex()
  {
    $redirecter = new sfSympalRedirecter($this);
    $redirecter->redirect();
  }
}
