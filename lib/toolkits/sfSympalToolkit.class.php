<?php
class sfSympalToolkit
{
  protected static
    $_currentMenuItem,
    $_currentContent;

  public static function getCurrentMenuItem()
  {
    return self::$_currentMenuItem;
  }

  public static function setCurrentMenuItem(MenuItem $menuItem)
  {
    self::$_currentMenuItem = $menuItem;
  }

  public static function getCurrentContent()
  {
    return self::$_currentContent;
  }

  public static function setCurrentContent(Content $content)
  {
    self::$_currentContent = $content;
  }

  public static function processPhpCode($code, $variables = array())
  {
    $sf_context = sfContext::getInstance();
    $vars = array(
      'sf_request' => $sf_context->getRequest(),
      'sf_response' => $sf_context->getResponse(),
      'sf_user' => $sf_context->getUser()
    );
    $variables = array_merge($variables, $vars);
    foreach ($variables as $name => $variable)
    {
      $$name = $variable;
    }

    ob_start();
    $code = str_replace('[?php', '<?php', $code);
    $code = str_replace('?]', '?>', $code);
    eval("?>" . $code);
    $rendered = ob_get_contents();
    ob_end_clean();

    return $rendered;
  }

  public static function loadDefaultLayout()
  {
    return self::changeLayout(sfSympalConfig::get('default_layout'));
  }

  public static function changeLayout($name)
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $response = sfContext::getInstance()->getResponse();
    $configuration = $context->getConfiguration();

    $actionEntry = $context->getController()->getActionStack()->getLastEntry();
    $module = $actionEntry ? $actionEntry->getModuleName():$request->getParameter('module');
    $action = $actionEntry ? $actionEntry->getActionName():$request->getParameter('action');

    $bundledLayout = false;
    if (file_exists($name))
    {
      $fullPath = $name;
    } else if (file_exists($path = sfConfig::get('sf_app_dir').'/templates/'.$name.'.php')) {
      $fullPath = $path;
    } else {
      $path = $configuration->getPluginConfiguration('sfSympalPlugin')->getRootDir() . '/templates/' . $name;
      $bundledLayout = true;
    }

    if (isset($fullPath) && file_exists($fullPath))
    {
      $e = explode('.', $fullPath);
      $path = $e[0];
      $info = pathinfo($fullPath);
      $name = $info['filename'];
    }

    sfConfig::set('symfony.view.'.$module.'_'.$action.'_layout', $path);
    sfConfig::set('symfony.view.sympal_default_error404_layout', $path);
    sfConfig::set('symfony.view.sympal_default_secure_layout', $path);

    if ($bundledLayout)
    {
      $response->addStylesheet('/sfSympalPlugin/css/global');
      $response->addStylesheet('/sfSympalPlugin/css/default');
      $response->addStylesheet('/sfSympalPlugin/css/' . $name);
    } else {
      $response->addStylesheet($name, 'last');
    }
  }

  public static function isEditMode()
  {
    $user = sfContext::getInstance()->getUser();

    return $user->isAuthenticated() && $user->getAttribute('sympal_edit', false);
  }

  public static function generateBreadcrumbs($breadcrumbsArray)
  {
    $breadcrumbs = new sfSympalMenuBreadcrumbs('Breadcrumbs');

    $count = 0;
    $total = count($breadcrumbsArray);
    foreach ($breadcrumbsArray as $name => $route)
    {
      $count++;
      if ($count == $total)
      {
        $breadcrumbs->addChild($name);
      } else {
        $breadcrumbs->addChild($name, $route);
      }
    }

    return $breadcrumbs;
  }

  public static function askConfirmation($title, $message)
  {
    $context = sfContext::getInstance();
    $request = $context->getRequest();
    $action = $context->getController()->getActionStack()->getLastEntry()->getActionInstance();

    if ($request->hasParameter('confirmation'))
    {
      if ($request->getParameter('yes'))
      {
        return true;
      } else {
        $action->redirect($request->getParameter('redirect_url'));
      }
    } else {
      $request->setAttribute('title', $title);
      $request->setAttribute('message', $message);

      $action->forward('sympal_default', 'ask_confirmation');
    }
  }

  public static function sendEmail($name, $vars = array())
  {
    $e = explode('/', $name);
    list($module, $action) = $e;

    try {
      $rawEmail = self::getEmailPresentationFor($module, $action, $vars);
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

    $mailer->send($message, $vars['email_address'], sfSympalConfig::get('default_from_email_address', null, 'jonwage@gmail.com'));
    $mailer->disconnect();

    sfContext::getInstance()->getLogger()->debug($emailBody);
  }

  public static function getEmailPresentationFor($module, $action, $vars = array())
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

  public static function getContentTypesCache()
  {
    $cachePath = sfConfig::get('sf_cache_dir').'/sympal/content_types.cache';
    if (file_exists($cachePath))
    {
      return unserialize(file_get_contents($cachePath));
    } else {
      return array();
    }
  }
}