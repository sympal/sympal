<?php

/**
 * Listens to template.filter_parameters to add the following variables to the view:
 *   * sf_sympal_context
 *   * sf_sympal_content
 *   * sf_sympal_menu_item
 * 
 * @package     
 * @subpackage  
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalTemplateFilterParametersListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'template.filter_parameters';
  }

  /**
   * @TODO How does this compare with the variables passed to the view
   * via sfSympalContentRenderer. This seems more all-encompassing, but
   * still possibly redundant.
   */
  public function run(sfEvent $event, $parameters)
  {
    $sympalContext = $this->_invoker;

    $parameters['sf_sympal_context'] = $sympalContext;
    
    $parameters['sf_sympal_site'] = $sympalContext->getService('site_manager')->getSite();

    if ($content = $sympalContext->getService('site_manager')->getCurrentContent())
    {
      $parameters['sf_sympal_content'] = $content;
    }

    if ($menuItem = $sympalContext->getService('menu_manager'))
    {
      $parameters['sf_sympal_menu_manager'] = $menuItem;
    }

    return $parameters;
  }
}