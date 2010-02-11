<?php

/**
 * Class responsible for running one single check for a sfSympalServerCheck instance
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
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

  /**
   * Get the name of the check
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->_name;
  }

  /**
   * Get the requirement of the check
   *
   * @return string $requirement
   */
  public function getRequirement()
  {
    return $this->_requirement;
  }

  /**
   * Get the state of the check
   *
   * @return string $state
   */
  public function getState()
  {
    return $this->_state;
  }

  /**
   * Get the level of the check
   *
   * @return string $level
   */
  public function getLevel()
  {
    return $this->_level;
  }

  /**
   * Check whether this check is required or not
   *
   * @return boolean
   */
  public function isRequired()
  {
    return sfSympalServerCheck::ERROR === $this->_level;
  }

  /**
   * Get the diagnostic value for this check
   *
   * @return string $diagnostic
   */
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

  /**
   * Check if this server check unit passes or not
   *
   * @return boolean
   */
  public function pass()
  {
    if ($this->isType(self::TYPE_BOOL))
    {
      return $this->_state == $this->_requirement;
    }

    return version_compare($this->_state, $this->_requirement) >= 0;
  }

  /**
   * Check whether this server check unit is of the given type
   *
   * @param string $type 
   * @return boolean
   */
  public function isType($type)
  {
    return $this->getType() == $type;
  }

  /**
   * Get the type of this server check unit
   *
   * @return integer $type
   */
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

  /**
   * __toString() implementation that returns the name of this server check unit
   *
   * @see getName()
   * @return string $name
   */
  public function __toString()
  {
    return $this->getName();
  }
}