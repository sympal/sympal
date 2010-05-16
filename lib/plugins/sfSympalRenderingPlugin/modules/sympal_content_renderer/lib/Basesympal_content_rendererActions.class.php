<?php

/**
 * Actions class for the rendering of most Content records
 * 
 * @package     sfSympalRenderingPlugin
 * @subpackage  actions
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class Basesympal_content_rendererActions extends sfActions
{

  /**
   * Specified in app.yml as default_rendering_module and default_rendering_action
   * 
   * By default, all Content routes are pointed here. The content action
   * loader then processes everything needed to render the content
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->renderer = $this->getSympalContentActionLoader()->loadContentRenderer();
  }
}