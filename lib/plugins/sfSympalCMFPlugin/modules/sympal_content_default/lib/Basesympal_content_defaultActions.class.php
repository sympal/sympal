<?php

/**
 * Actions class for sympal_content_default
 * 
 * @package     sfSympalCMFPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 * @since       2010-04-02
 * @version     svn:$Id$ $Author$
 */
class Basesympal_content_defaultActions extends sfActions
{
  /**
   * The default module for handling requests that go to an unpublished
   * piece of content
   */
  public function executeUnpublished_content(sfWebRequest $request)
  {
  }

  public function executeNew_site(sfWebRequest $request)
  {
    $this->loadSiteTheme();
  }
}