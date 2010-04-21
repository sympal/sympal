<?php

/**
 * Class responsible for rendering the results of a sfSympalServerCheck instance
 * to the command line from a Symfony sfTask instance
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalServerCheckCliRenderer extends sfSympalServerCheckRenderer
{
  protected
    $_task,
    $_rowWidth = array(30, 25, 25, 12),
    $_errors,
    $_warnings,
    $_checks;

  /**
   * Set the task that this class is rendering for
   *
   * @param sfTask $task 
   * @return void
   */
  public function setTask(sfTask $task)
  {
    $this->_task = $task;
  }

  /**
   * Render all the results
   *
   * @return void
   */
  public function render()
  {
    $this->_task->logBlock('Checking server requirements for Symfony & Sympal', 'INFO_LARGE');

    foreach ($this->_check->getChecks() as $checkSpace => $checks)
    {
      $this->_renderHead($checkSpace);

      foreach($checks as $unit)
      {
        $this->_renderCheck($unit);

        if ('error' === $unit->getDiagnostic())
        {
          $this->_errors[] = $unit;
        }
        elseif ('warning' === $unit->getDiagnostic())
        {
          $this->_warnings[] = $unit;
        }

        $this->_checks[] = $unit;

        usleep(200000);
      }
    }

    if (count($this->_warnings))
    {
      $this->_task->logBlock(count($this->_warnings).' warnings : '.implode(', ', $this->_warnings), 'COMMENT_LARGE');
    }
    if (count($this->_errors))
    {
      $this->_task->logBlock(sprintf('%d/%d check(s) failed : '.implode(', ', $this->_errors), count($this->_errors), count($this->_checks)), 'ERROR_LARGE');
    }
    else
    {
      $this->_task->logBlock('The server matches Symfony & Sympal requirements', 'INFO_LARGE');
    }

    if (count($this->_errors))
    {
      throw new sfException('Sympal can NOT run safely in this environment');
    }
  }

  /**
   * Render the results header
   *
   * @param string $space The check space name
   * @return string $head
   */
  protected function _renderHead($space)
  {
    return $this->_task->logBlock($this->_renderLine(array_map('strtoupper', array(
      $space,
      'Requirement',
      'Server state',
      'Diagnostic'
    ))), 'COMMENT_LARGE');
  }

  /**
   * Render an individual check
   *
   * @param sfSympalServerCheckUnit $unit 
   * @return void
   */
  protected function _renderCheck(sfSympalServerCheckUnit $unit)
  {
    $line = $this->_renderLine(array(
      $this->_renderName($unit),
      $this->_renderRequirement($unit),
      $this->_renderState($unit),
      str_replace('valid', 'ok', $unit->getDiagnostic())
    ));

    switch($unit->getDiagnostic())
    {
      case 'valid':
        $this->_task->logBlock($line, 'INFO');
      break;

      case 'warning':
        $this->_task->log(' '.$line);
      break;

      case 'error':
        $this->_task->logBlock($line, 'ERROR');
      break;
    }
  }

  /**
   * Render a line from an array of values
   *
   * @param array $values 
   * @return string $line
   */
  protected function _renderLine(array $values)
  {
    $text = '';

    foreach($values as $index => $value)
    {
      $text .= str_repeat(' ', max(0, $this->_rowWidth[$index] - strlen($value))).$value;
    }

    return $text;
  }
}