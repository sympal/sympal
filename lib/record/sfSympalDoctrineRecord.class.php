<?php

abstract class sfSympalDoctrineRecord extends sfDoctrineRecord
{
  protected $_oldValues = array();
  protected $_newValues = array();

  public function set($fieldName, $value, $load = true)
  {
    $this->_handleVersioning($fieldName, $value);

    return parent::set($fieldName, $value, $load);
  }

  public function isValueModified($type, $oldValue, $newValue)
  {
    return $this->_isValueModified($type, $oldValue, $newValue);
  }

  protected function _handleVersioning($fieldName, $newValue)
  {
    if (!sfSympalConfig::isVersioningEnabled($this) || !is_scalar($newValue) || sfSympalConfig::get('installing'))
    {
      return false;
    }

    if (isset($this->_values[$fieldName]))
    {
      $oldValue = isset($this->_values[$fieldName]) ? $this->_values[$fieldName]:null;
    } else {
      $oldValue = isset($this->_data[$fieldName]) ? $this->_data[$fieldName]:null;
    }
    
    $this->_oldValues[$fieldName] = $oldValue;
    $this->_newValues[$fieldName] = $newValue;
  }

  public function getOldValues()
  {
    return $this->_oldValues;
  }

  public function getNewValues()
  {
    return $this->_newValues;
  }

  public function resetVersioningValues()
  {
    $this->_oldValues = array();
    $this->_newValues = array();
  }

  public function __call($method, $arguments)
  {
    try {
      if (in_array($verb = substr($method, 0, 3), array('set', 'get')))
      {
        $name = substr($method, 3);

        $table = $this->getTable();
        if ($table->hasRelation($name))
        {
          $entityName = $name;
        }
        else if ($table->hasField($fieldName = $table->getFieldName($name)))
        {
          $entityName = strtolower($fieldName);
        }
        else
        {
          $underScored = $table->getFieldName(sfInflector::underscore($name));
          if ($table->hasField($underScored))
          {
            $entityName = $underScored;
          } else if ($table->hasField(strtolower($name))) {
            $entityName = strtolower($name);
          } else {
            $entityName = $underScored;
          }
        }

        $result = call_user_func_array(
          array($this, $verb),
          array_merge(array($entityName), $arguments)
        );
      } else {
        $result = false;
      }
    } catch(Exception $e) {
      $result = false;
    }

    if (!$result)
    {
      if (($template = $this->_table->getMethodOwner($method)) !== false)
      {
        $template->setInvoker($this);
        return call_user_func_array(array($template, $method), $arguments);
      }

      foreach ($this->_table->getTemplates() as $template)
      {
        if (is_callable(array($template, $method)))
        {
          $template->setInvoker($this);

          try {
            $result = call_user_func_array(array($template, $method), $arguments);
          } catch (Exception $e) {
            $result = null;
          }

          if (!is_null($result))
          {
            $this->_table->setMethodOwner($method, $template);

            return $result;
          }
        }
      }
    } else {
      return $result;
    }
  }
}