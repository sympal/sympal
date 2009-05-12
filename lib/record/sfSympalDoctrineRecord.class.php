<?php

abstract class sfSympalDoctrineRecord extends sfDoctrineRecord
{
  protected
    $_export = array(),
    $_dontExport = array(),
    $_oldValues = array(),
    $_newValues = array();

  public function hasField($name)
  {
    $result = false;
    if ($this->getTable()->hasField($name))
    {
      $result = true;
    }

    if ($this->getTable()->hasRelation('Translation') && $this->getTable()->getRelation('Translation')->getTable()->hasField($name))
    {
      $result = true;
    }

    return $result;
  }

  protected function _isPropertyExportable($property)
  {
    return ((empty($this->_export) || in_array($property, $this->_export)) && 
      !in_array($property, $this->_dontExport)) ? true:false;
  }

  public function exportTo($type, $deep = true)
  {
      if ($type == 'array') {
          return $this->exportData($deep);
      } else {
          return Doctrine_Parser::dump($this->exportData($deep, true), $type);
      }
  }

  public function exportData($deep = true, $prefixKey = false)
  {
    if ($this->_state == self::STATE_LOCKED || $this->_state == self::STATE_TLOCKED)
    {
      return false;
    }

    $stateBeforeLock = $this->_state;
    $this->_state = $this->exists() ? self::STATE_LOCKED : self::STATE_TLOCKED;

    $a = array();

    foreach ($this as $column => $value)
    {
      if ($value === self::$_null || is_object($value))
      {
        $value = null;
      }

      if ($this->_isPropertyExportable($column))
      {
        $a[$column] = $this->get($column);
      }
    }

    if ($this->_table->getIdentifierType() ==  Doctrine::IDENTIFIER_AUTOINC)
    {
      if ($this->_isPropertyExportable($column))
      {
        $i      = $this->_table->getIdentifier();
        $a[$i]  = $this->getIncremented();
      }
    }

    if ($deep)
    {
      foreach ($this->_references as $key => $relation)
      {
        if (! $relation instanceof Doctrine_Null && $this->_isPropertyExportable($key))
        {
          if ($relation instanceof Doctrine_Record)
          {
            $a[$key] = $relation->exportData($deep, $prefixKey);
          } else {
            foreach ($relation as $k => $v)
            {
              $a[$key][get_class($v).'_'.implode('_', $v->identifier())] = $v->exportData($deep, $prefixKey);
            }
          }
        }
      }
    }

    // [FIX] Prevent mapped Doctrine_Records from being displayed fully
    foreach ($this->_values as $key => $value)
    {
      if ($this->_isPropertyExportable($key))
      {
        if ($value instanceof Doctrine_Record)
        {
          $a[$key] = $value->exportData($deep, $prefixKey);
        } else {
          $a[$key] = $value;
        }
      }
    }
    
    $this->_state = $stateBeforeLock;

    return $a;
  }

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