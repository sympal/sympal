<?php

class sfSympalRedirectRouteTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application.'),
      new sfCommandArgument('route', sfCommandArgument::REQUIRED, 'The route to redirect.'),
      new sfCommandArgument('destination', sfCommandArgument::REQUIRED, 'The destination to redirect to.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'redirect-route';
    $this->briefDescription = 'Redirect a Symfony route to another route, URL or path.';

    $this->detailedDescription = <<<EOF
The [symfony sympal:redirect-route|INFO] task redirects a Symfony route to another route, URL or path.

  [./symfony sympal:redirect-route |INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $context = sfContext::createInstance($this->configuration);
    $configCache = $context->getConfigCache();
    $context->getRouting()->loadConfiguration();

    $routes = $context->getRouting()->getRoutes();

    if (!isset($routes[$arguments['route']]))
    {
      throw new InvalidArgumentException(sprintf('Could not find route named "%s"', $arguments['route']));
    }

    $redirect = new sfSympalRedirect();
    $redirect->source = $routes[$arguments['route']]->getPattern();

    if (is_numeric($arguments['destination']))
    {
      $this->logSection('sympal', sprintf('Redirecting route "%s" to content id "%s"', $routes[$arguments['route']]->getPattern(), $arguments['destination']));

      $redirect->content_id = $arguments['destination'];
    } else {
      $this->logSection('sympal', sprintf('Redirecting route "%s" to "%s"', $routes[$arguments['route']]->getPattern(), $arguments['destination']));

      $redirect->destination = $arguments['destination'];
    }

    $redirect->Site = Doctrine_Core::getTable('sfSympalSite')->findOneBySlug($arguments['application']);
    $redirect->save();

    $this->clearCache();
  }
}