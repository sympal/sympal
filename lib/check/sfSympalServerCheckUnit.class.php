<?php

class sfSympalServerCheckUnit
{
  const TYPE_BOOL = 1;
  const TYPE_BYTE = 2;
  const TYPE_VERSION = 3;

  protected
    $_name,
    $_requirement,
    $_state,
    $_level;

  public function __construct($name, $state, $requirement, $level = sfSympalServerCheck::WARNING)
  {
    $this->_name = $name;
    $this->_state = $state;
    $this->_requirement = $requirement;
    $this->_level = $level;
  }

  public function getName()
  {
    return $this->_name;
  }

  public function getRequirement()
  {
    return $this->_requirement;
  }

  public function getState()
  {
    return $this->_state;
  }

  public function getLevel()
  {
    return $this->_level;
  }

  public function isRequired()
  {
    return sfSympalServerCheck::ERROR === $this->_level;
  }

  public function getDiagnostic()
  {
    if ($this->pass())
    {
      $diagnostic = 'valid';
    }
    else
    {
      $diagnostic = $this->_level == sfSympalServerCheck::WARNING ? 'warning' : 'error';
    }

    return $diagnostic;
  }

  public function pass()
  {
    if ($this->isType(self::TYPE_BOOL))
    {
      return $this->_state == $this->_requirement;
    }

    return version_compare($this->_state, $this->_requirement) >= 0;
  }

  public function isType($type)
  {
    return $this->getType() == $type;
  }

  public function getType()
  {
    if (is_bool($this->_requirement))
    {
      return self::TYPE_BOOL;
    }
    if (is_integer($this->_requirement))
    {
      return self::TYPE_BYTE;
    }

    return self::TYPE_VERSION;
  }

  public function __toString()
  {
    return $this->getName();
  }
}