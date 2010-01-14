<?php

require_once dirname(__FILE__).'/sympal_redirectsGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/sympal_redirectsGeneratorHelper.class.php';

/**
 * Base actions for the sfSympalAdminPlugin sympal_redirects module.
 * 
 * @package     sfSympalAdminPlugin
 * @subpackage  sympal_redirects
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_redirectsActions extends autoSympal_redirectsActions
{
  public function executeCreate(sfWebRequest $request)
  {
    $this->form = $this->configuration->getForm();
    $this->sf_sympal_redirect = $this->form->getObject();
    $this->sf_sympal_redirect->site_id = sfSympalContext::getInstance()->getSite()->getId();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }
}
