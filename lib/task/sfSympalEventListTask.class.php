<?php

class sfSympalEventListTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application.'),
      new sfCommandArgument('url', sfCommandArgument::OPTIONAL, 'The url to check for available events'),
    ));

    $this->addOptions(array(
      new sfCommandOption('all', null, sfCommandOption::PARAMETER_NONE, 'Show a list of every single notified event name and where it is in the code.', null),
      new sfCommandOption('event', null, sfCommandOption::PARAMETER_OPTIONAL, 'The event name to inspect', null),
      new sfCommandOption('show-connected', null, sfCommandOption::PARAMETER_NONE, 'Only show events that have been connected to', null),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'event-list';
    $this->briefDescription = 'List of available events for a given url.';

    $this->detailedDescription = <<<EOF
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if (isset($options['all']) && $options['all'])
    {
      $dirs = $this->configuration->getPluginPaths();
      $dirs[] = sfConfig::get('sf_symfony_lib_dir');
      $dirs[] = sfConfig::get('sf_lib_dir');

      $files = sfFinder::type('file')->name('*.php')->in($dirs);

      foreach ($files as $file)
      {
        $code = file_get_contents($file);
        $lines = explode("\n", $code);
        foreach ($lines as $key => $line)
        {
          $lineNumber = $key + 1;
          preg_match_all("/([notify, filter, notifyUntil]+)\(new sfEvent\((.*)\)\)/", $line, $matches);

          if (isset($matches[0][0]))
          {
            $e = explode(', ', $matches[2][0]);
            $subject = $e[0];
            $name = trim($e[1], "')(");
            $type = $matches[1][0];
            if ($name == 'application.log' || $name == 'command.log')
            {
              continue;
            }
            $path = str_replace(sfConfig::get('sf_symfony_lib_dir').'/', '', $file);
            $lineNumber = $this->formatter->format('L#'.$lineNumber, 'INFO');
            $name = $this->formatter->format(str_pad($name, 40, ' ', STR_PAD_RIGHT), 'COMMENT');
            $type = $this->formatter->format(str_pad($type, 15, ' ', STR_PAD_RIGHT), 'INFO');
            $this->log($type.' '.$name.' ('.$path.') '.$lineNumber);
          }
        }
      }
    } else {
      $dispatcher = new sfSympalEventListerDispatcher();
      $configuration = ProjectConfiguration::getApplicationConfiguration($arguments['application'], 'dev', true, null, $dispatcher);
      $context = sfContext::createInstance($configuration);

      $user = Doctrine_Core::getTable(sfSympalConfig::get('user_model'))->findOneByIsSuperAdmin(true);
      $browser = new sfSympalEventListerBrowser(null, null, null, $context);
      $browser->getUser()->setAuthenticated(true);
      $browser->getUser()->signin($user);
      $browser->get($arguments['url']);

      $events = $this->_buildEvents($dispatcher);
      $this->log(null);
      if (isset($options['event']) && $options['event'])
      {
        if (!isset($events[$options['event']]))
        {
          throw new sfException('Could not find route named "'.$options['event'].'"');
        }
        $connects = $events[$options['event']];
        $this->logSection('Found event named "'.$options['event'].'"', null);
        $this->log('Event connected '.count($connects).' times');
        $this->log(null);
        foreach ($connects as $connect)
        {
          $this->log('              '.$connect['callable'].' (Line #'.$connect['line'].')');
        }
      } else {
        $messages = array();
        foreach ($events as $name => $connects)
        {
          if (isset($options['show-connected']) && $options['show-connected'] && !count($connects))
          {
            continue;
          }
          $num =count($connects);
          $messages[] = '   ('.$num.') '.$name;
        }

        $this->logSection(count($messages).' events found for the url "'.$arguments['url'].'"', null);
        $this->log('');
        foreach ($messages as $message)
        {
          $this->log($message);
        }
      }
      $this->log(null);
    }
  }

  protected function _buildEvents($eventDispatcher)
  {
    $allEvents = $eventDispatcher->getEvents();
    $events = array();
    foreach ($allEvents as $key => $event)
    {
      if ($event->getName() != 'application.log')
      {
        $events[] = $event->getName();
      }
    }

    $events = array_unique($events);
    $connected = $eventDispatcher->getConnected();

    $final = array();
    foreach ($events as $event)
    {
      $num = isset($connected[$event]) ? count($connected[$event]):0;
      $connects = array();
      if ($num)
      {
        foreach ($connected[$event] as $connect)
        {
          $class = is_object($connect[0]) ? get_class($connect[0]):$connect[0];
          $refClass = new ReflectionClass($class);
          $method = $refClass->getMethod($connect[1]);
          $connects[] = array('callable' => $class.'::'.$connect[1], 'line' => $method->getStartLine());
        }
      }
      $final[$event] = $connects;
    }
    return $final;
  }
}

class sfSympalEventListerBrowser extends sfBrowser
{
  public function __construct($hostname = null, $remote = null, $options = array(), $context = null)
  {
    $this->context = $context;
    $this->initialize($hostname, $remote, $options);
  }

  public function getContext($forceReload = false)
  {
    return $this->context;
  }

}
class sfSympalEventListerDispatcher extends sfEventDispatcher
{
  protected
    $_events = array(),
    $_connected = array();
  
  public function getEvents()
  {
    return $this->_events;
  }

  public function connect($name, $listener)
  {
    if (!isset($this->_connected[$name]))
    {
      $this->_connected[$name] = array();
    }

    $this->_connected[$name][] = $listener;
    parent::connect($name, $listener);
  }

  public function getConnected()
  {
    return $this->_connected;
  }

  public function notifyUntil(sfEvent $event)
  {
    $this->_events[] = $event;
    return parent::notifyUntil($event);
  }

  public function notify(sfEvent $event)
  {
    $this->_events[] = $event;
    return parent::notify($event);
  }
}