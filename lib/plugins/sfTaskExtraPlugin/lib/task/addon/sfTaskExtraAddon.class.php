<?php

require_once dirname(__FILE__).'/../sfTaskExtraBaseTask.class.php';

/**
 * Base addon class.
 * 
 * @package     sfTaskExtraPlugin
 * @subpackage  task
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id: sfTaskExtraAddon.class.php 25056 2009-12-07 22:32:45Z Kris.Wallsmith $
 */
abstract class sfTaskExtraAddon extends sfTaskExtraBaseTask
{
  protected
    $wrappedTask = null;

  /**
   * Constructor.
   * 
   * @see sfTask
   */
  public function __construct(sfProjectConfiguration $configuration, sfFormatter $formatter)
  {
    parent::__construct($configuration->getEventDispatcher(), $formatter);
    $this->configuration = $configuration;
    $this->pluginConfiguration = $configuration->getPluginConfiguration('sfTaskExtraPlugin');
  }

  /**
   * Executes the extra.
   * 
   * @param array $arguments
   * @param array $options
   */
  abstract public function executeAddon($arguments = array(), $options = array());

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    throw new sfException('You can\'t execute this task.');
  }

  /**
   * Sets the task the current extra wraps.
   * 
   * @param sfTask $task
   */
  public function setWrappedTask(sfTask $task)
  {
    $this->wrappedTask = $task;
  }

  /**
   * @see sfTask
   */
  public function log($messages)
  {
    null === $this->wrappedTask ? parent::log($message) : $this->wrappedTask->log($message);
  }

  /**
   * @see sfTask
   */
  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    null === $this->wrappedTask ? parent::logSection($section, $message, $size, $style) : $this->wrappedTask->logSection($section, $message, $size, $style);
  }

  /**
   * @see sfBaseTask
   */
  public function getFilesystem()
  {
    return $this->wrappedTask instanceof sfBaseTask ? $this->wrappedTask->getFilesystem() : new sfFilesystem();
  }
}
