<?php

class sfSympalServerCheckTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'server-check';
    $this->briefDescription = 'Check that the current server environment can run Sympal properly.';

    $this->detailedDescription = <<<EOF
The [symfony sympal:server-check|INFO] task checks that the current server environment can run Sympal properly.

  [./symfony sympal:server-check|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $check = new sfSympalServerCheck();
    $renderer = new sfSympalServerCheckCliRenderer($check);
    $renderer->setTask($this);
    $renderer->render();
  }
}