<?php

class sfSympalEventListTask extends sfTaskExtraBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('url', sfCommandArgument::REQUIRED, 'The url to check for available events'),
    ));

    $this->addOptions(array(
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
    $dispatcher = new sfSympalEventListerDispatcher();
    $configuration = ProjectConfiguration::getApplicationConfiguration('sympal', 'dev', true, null, $dispatcher);
    $context = sfContext::createInstance($configuration);

    $user = Doctrine_Core::getTable('User')->findOneByIsSuperAdmin(true);
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