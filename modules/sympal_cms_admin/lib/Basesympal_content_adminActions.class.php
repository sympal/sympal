<?php

/**
 * Generic admin actions related to the CMF layer
 * 
 * @package     sfSympalPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class Basesympal_content_adminActions extends sfActions
{

  /**
   * Action that tests your current server setup and outputs a report
   * of what passes / fails the tests
   */
  public function executeCheck_server(sfWebRequest $request)
  {
    $this->getResponse()->setTitle(__('Sympal Admin / Check Server'));

    $check = new sfSympalServerCheck();
    $this->renderer = new sfSympalServerCheckHtmlRenderer($check);
  }
}