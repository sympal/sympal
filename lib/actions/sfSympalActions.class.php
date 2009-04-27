<?php

class sfSympalActions extends sfSympalExtendClass
{
  public function checkFilePermissions()
  {
    $checks = sfFinder::type('file')->in(sfConfig::get('sf_root_dir'));
    $checks = array_merge($checks, sfFinder::type('dir')->in(sfConfig::get('sf_root_dir')));

    $error = false;
    foreach ($checks as $check)
    {
      if (!is_writable($check))
      {
        $error = true;
      }
    }
    if ($error)
    {
      $this->getUser()->setFlash('error', 'You have some permissions problems. Sympal requires your project to be writable by your web server.');
    }

    if ($error)
    {
      return false;
    } else {
      return true;
    }
  }

  public function changeLayout($name)
  {
    return sfSympalToolkit::changeLayout($name);
  }

  public function loadDefaultLayout()
  {
    return sfSympalToolkit::loadDefaultLayout();
  }

  public function askConfirmation($title, $message, $variables = array())
  {
    $e = explode('/', $message);
    if (count($e) == 2)
    {
      try {
        $message = sfSympalToolkit::getSymfonyResource($e[0], $e[1], $variables);
      } catch (Exception $e) {
        throw new sfException('Invalid confirmation message: '.$e->getMessage());
      }
    }

    $request = $this->getRequest();

    if ($request->hasParameter('sympal_ask_confirmation') && $request->getParameter('sympal_ask_confirmation'))
    {
      if ($request->getParameter('yes'))
      {
        return true;
      } else {
        $this->redirect($request->getParameter('redirect_url'));
      }
    } else {
      $this->getResponse()->setTitle($title);
      $request->setAttribute('title', $title);
      $request->setAttribute('message', $message);

      $this->forward('sympal_default', 'ask_confirmation');
    }
  }

  public function getEmailPresentationFor($module, $action, $variables = array())
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Partial'));

    $email = sfSympalToolkit::getSymfonyResource($model, $action, $variables);

    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($email, 'sympal.filter_email_presentation', array('module' => $module, 'action' => $action, 'variables' => $variables)));

    if ($event->isProcessed() && $event->getReturnValue())
    {
      $email = $event->getReturnValue();
    }

    return $email;
  }

  public function newEmail($name, $variables = array())
  {
    $e = explode('/', $name);
    list($module, $action) = $e;

    try {
      $rawEmail = $this->getEmailPresentationFor($module, $action, $variables);
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

    $this->mail = new sfSympalMail();
    $this->message = $this->mail->setMessage($emailSubject, $emailBody, 'text/html');

    return $this->mail;
  }

  public function forwardToRoute($full)
  {
    if (strstr($full, '?'))
    {
      $pos = strpos($full, '?');
      $route = substr($full, 1, $pos - 1);
    } else {
      $route = substr($full, 1, strlen($full));
    }

    $r = $this->getContext()->getRouting();
    $routes = $r->getRoutes();
    if ( ! isset($routes[$route]))
    {
      throw new sfException('Could not find route named: "' . $route . '"');
    }

    if (isset($pos))
    {
      $params = substr($full, $pos + 1, strlen($full));
      $e = explode('&', $params);

      foreach ($e as $param)
      {
        $e2 = explode('=', $param);
        if ((isset($e2[0]) && $e2[0]) && (isset($e2[1]) && $e2[1]))
        {
          $this->getRequest()->setParameter($e2[0], $e2[1]);
        }
      }
    }

    $routeInfo = $routes[$route];
    $params = $routeInfo[3];

    foreach ($params as $key => $value)
    {
      if ($value)
      {
        $this->getRequest()->setParameter($key, $value);
      }
    }

    $this->forward($params['module'], $params['action']);
  }

  public function goBack()
  {
    $this->redirect($this->getRequest()->getReferer());
  }
}