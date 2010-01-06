<?php

class sfSympalServerCheckRenderer
{
  protected $_check;

  public function __construct(sfSympalServerCheck $check)
  {
    $this->_check = $check;
  }

  protected function _renderName(sfSympalServerCheckUnit $unit)
  {
    return $unit->getName();
  }

  protected function _renderRequirement(sfSympalServerCheckUnit $unit)
  {
    return $this->_renderValue($unit->getRequirement(), $unit);
  }

  protected function _renderState(sfSympalServerCheckUnit $unit)
  {
    return $this->_renderValue($unit->getState(), $unit);
  }

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

  protected function _renderDiagnostic(sfSympalServerCheckUnit $unit)
  {
    return $unit->getDiagnostic();
  }
}