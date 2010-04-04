<?php

/**
 * Listens to template.filter_parameters to add the following variables to the view:
 *   * sf_sympal_context
 * 
 * @package     sfSympalPlugin
 * @subpackage  events
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

    return $parameters;
  }
}