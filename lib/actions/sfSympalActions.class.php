<?php

class sfSympalActions
{
  protected $_actions;

  public static function handleAction(sfEvent $event)
  {
    $method = $event['method'];
    $arguments = $event['arguments'];

    $instance = new sfSympalActions();
    $instance->_actions = $event->getSubject();

    return call_user_func_array(array($instance, $method), $arguments);
  }

  public function changeLayout($name)
  {
    return sfSympalToolkit::changeLayout($name);
  }

  public function loadDefaultLayout()
  {
    return sfSympalToolkit::loadDefaultLayout();
  }

  public function askConfirmation($title, $message)
  {
    $request = $this->getRequest();

    if ($request->hasParameter('confirmation'))
    {
      if ($request->getParameter('yes'))
      {
        return true;
      } else {
        $this->redirect($request->getParameter('redirect_url'));
      }
    } else {
      $request->setAttribute('title', $title);
      $request->setAttribute('message', $message);

      $this->forward('sympal_default', 'ask_confirmation');
    }
  }

  public function getEmailPresentationFor($module, $action, $vars = array())
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Partial'));

    try {
      return get_partial($module.'/'.$action, $vars);
    } catch (Exception $e1) {
      try {
        return get_component($module, $action, $vars);
      } catch (Exception $e2) {
        throw new sfException('Could not find a partial or component for '.$module.' and '.$action.': '.$e1->getMessage().' '.$e2->getMessage());
      }
    }
  }

  public function sendEmail($name, $vars = array())
  {
    $e = explode('/', $name);
    list($module, $action) = $e;

    try {
      $rawEmail = $this->getEmailPresentationFor($module, $action, $vars);
    } catch (Exception $e) {
      throw new sfException('Could not send email: '.$e->getMessage());
    }

    if ($rawEmail)
    {
      $e = explode("\n", $rawEmail);
      
      $emailSubject = $e[0];
      unset($e[0]);
      $emailBody = implode("\n", $e);
    } else {
      $emailSubject = '';
      $emailBody = '';
    }

    $mailer = new Swift(new Swift_Connection_NativeMail());
    $message = new Swift_Message($emailSubject, $emailBody, 'text/html');

    $mailer->send($message, $vars['email_address'], sfSympalConfig::get('default_from_email_address', null, 'noreply@sympalphp.org'));
    $mailer->disconnect();

    $this->logMessage($emailBody, 'debug');

    return true;
  }

  public function __get($name)
  {
    return $this->_actions->$name;
  }

  public function __set($name, $value)
  {
    $this->_actions->$name = $value;
  }

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->_actions, $method), $arguments);
  }
}