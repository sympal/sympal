<?php

/**
 * Abstract class for renderers to inherit from for rendering the results
 * of a sfSympalServerCheck instance.
 *
 * @see sfSympalServerCheckCliRenderer
 * @see sfSympalServerCheckHtmlRenderer
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class sfSympalServerCheckRenderer
{
  protected $_check;

  public function __construct(sfSympalServerCheck $check)
  {
    $this->_check = $check;
  }

  /**
   * Render the results. Your renderer should define this method
   *
   * @return string $rendered
   */
  abstract public function render();

  /**
   * Render the name of a single server check unit
   *
   * @param sfSympalServerCheckUnit $unit 
   * @return string $name
   */
  protected function _renderName(sfSympalServerCheckUnit $unit)
  {
    return $unit->getName();
  }

  /**
   * Render the requirement value of a single server check unit
   *
   * @param sfSympalServerCheckUnit $unit 
   * @return string $requirement
   */
  protected function _renderRequirement(sfSympalServerCheckUnit $unit)
  {
    return $this->_renderValue($unit->getRequirement(), $unit);
  }

  /**
   * Render the state value of a single server check unit
   *
   * @param sfSympalServerCheckUnit $unit
   * @return string $state
   */
  protected function _renderState(sfSympalServerCheckUnit $unit)
  {
    return $this->_renderValue($unit->getState(), $unit);
  }

  /**
   * Render a value of a single server check unit
   *
   * @param string $value
   * @param sfSympalServerCheckUnit $unit 
   * @return string $renderedValue
   */
  protected function _renderValue($value, sfSympalServerCheckUnit $unit)
  {
    switch($unit->getType())
    {
      case sfSympalServerCheckUnit::TYPE_BOOL:
        $response = $value ? 'ON' : 'OFF';
      break;

      case sfSympalServerCheckUnit::TYPE_BYTE:
        $response = sfInflector::humanize($value);
      break;

      default:
        $response = $value ? $value : '-';
    }

    return $response;
  }

  /**
   * Render the diagnostic value of a single server check unit
   *
   * @param sfSympalServerCheckUnit $unit 
   * @return string $disgnostic
   */
  protected function _renderDiagnostic(sfSympalServerCheckUnit $unit)
  {
    return $unit->getDiagnostic();
  }
}