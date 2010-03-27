<?php
/**
 * Effectively extends sfForm to sfSympalForm
 * 
 * @package     sfSympalPlugin
 * @subpackage  events
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-27
 * @version     svn:$Id$ $Author$
 */
class sfSympalFormMethodNotFoundListener extends sfSympalListener
{
  public function getEventName()
  {
    return 'form.method_not_found';
  }

  public function run(sfEvent $event)
  {
    $sympalForm = new sfSympalForm();

    return $sympalForm->extend($event);
  }
}