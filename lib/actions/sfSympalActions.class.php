<?php

class sfSympalActions extends sfSympalExtendClass
{
  public function checkFilePermissions()
  {
    $items = array();

    if (file_exists(sfConfig::get('sf_upload_dir')))
    {
      $items[] = sfConfig::get('sf_upload_dir');
    }
    $items[] = sfConfig::get('sf_cache_dir');
    $items[] = sfConfig::get('sf_config_dir');
    $items[] = sfConfig::get('sf_data_dir');
    $items[] = sfConfig::get('sf_log_dir');
    $items[] = sfConfig::get('sf_lib_dir');
    $items[] = sfConfig::get('sf_plugins_dir');
    $items[] = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'symfony';

    $dirs = sfFinder::type('dir')->in($items);
    $files = sfFinder::type('file')->in($items);
    $checks = array_merge($dirs, $files);

    $error = false;
    foreach ($checks as $check)
    {
      if (!is_writable($check))
      {
        $error = true;
        break;
      }
    }

    if ($error)
    {
      $this->getUser()->setFlash('error', 'Sympal requires that some files and folders are writeable in your project. The file "'.$check.'" specifically is not writeable. Run the sympal:fix-perms task to fix or manually adjust the permissions.');
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

  public function getEmailTemplateFor($module, $action, $variables = array())
  {
    $variables = sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.email.filter_variables'), $variables)->getReturnValue();

    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Partial'));

    $email = sfSympalToolkit::getSymfonyResource($module, $action, $variables);

    $email = sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this, 'sympal.email.filter_template', array('module' => $module, 'action' => $action, 'variables' => $variables)), $email)->getReturnValue();

    return $email;
  }

  public function newEmail($name, $variables = array())
  {
    $e = explode('/', $name);
    list($module, $action) = $e;

    try {
      $rawEmail = $this->getEmailTemplateFor($module, $action, $variables);
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