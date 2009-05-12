<?php

/**
 * test actions.
 *
 * @package    sympal
 * @subpackage test
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class testActions extends sfActions
{
  public function executeAsk_confirmation(sfWebRequest $request)
  {
    $this->askConfirmation('Are you sure?', 'Are you sure you wish to execute this?');
  }

  public function executeNew_email(sfWebRequest $request)
  {
    $dispatcher = sfProjectConfiguration::getActive()->getEventDispatcher();
    $dispatcher->connect('sympal.email.filter_variables', array($this, 'filterEmailVariables'));

    $email = $this->newEmail('test/email', array('variable' => 'Test variable'));
    $request->setAttribute('email', $email);

    return sfView::NONE;
  }

  public function filterEmailVariables(sfEvent $event, $variables)
  {
    $variables['variable2'] = 'Test variable 2';

    return $variables;
  }

  public function executeForward_to_route()
  {
    $this->forwardToRoute('@forward_to_route?param1=value1&param2=value2');
  }

  public function executeForward_to_route2()
  {
    $this->forwardToRoute('@forward_to_route', array(
      'param1' => 'value1',
      'param2' => 'value2'
    ));
  }

  public function executeRoute_to_forward_to()
  {
    return sfView::NONE;
  }

  public function executeStart_go_back()
  {
    return sfView::NONE;
  }

  public function executeGo_back()
  {
    $this->goBack();
  }

  public function executeChange_layout()
  {
    $this->changeLayout('test');
  }
}