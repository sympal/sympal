<?php

/**
 * Base upgrade class
 * 
 * @package     sfSympalUpgradePlugin
 * @subpackage  upgrade
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @since       2010-03-26
 * @version     svn:$Id$ $Author$
 */
abstract class sfSympalUpgrade
{
  protected
    $_configuration,
    $_dispatcher,
    $_formatter;

  public function __construct(ProjectConfiguration $configuration, sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->_configuration = $configuration;
    $this->_dispatcher = $dispatcher;
    $this->_formatter = $formatter;
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    $this->_configuration->getEventDispatcher()->notify(new sfEvent($this, 'command.log', array($this->_formatter->formatSection($section, $message, $size, $style))));
  }

  public function upgrade()
  {
    return $this->_doUpgrade();
  }

  abstract protected function _doUpgrade();
}