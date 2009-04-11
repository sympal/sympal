<?php

class sfSympalRecord extends Doctrine_Template
{
  protected $_eventName;

  public function setInvoker(Doctrine_Record_Abstract $invoker)
  {
    parent::setInvoker($invoker);
    $this->_eventName = sfInflector::tableize(get_class($invoker));
  }

  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalRecordEventFilter());

    if ($this->isVersioned())
    {
      $this->sympalActAs('sfSympalVersionable');
    }

    if ($this->isI18ned())
    {
      $this->sympalActAs('sfSympalI18n', array('fields' => $this->getI18nedFields()), 'Doctrine_Template_I18n');
    }

    if ($this->isContent())
    {
      $this->sympalActAs('sfSympalContentTemplate');
    }

    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.set_table_definition', array('object' => $this)));
  }

  public function sympalActAs($tpl, $options = array(), $name = null)
  {
    if (is_string($tpl))
    {
      $tpl = new $tpl($options);
    }

    if (is_null($name))
    {
      $name = get_class($tpl);
    }

    $this->_table->addTemplate($name, $tpl);

    $tpl->setInvoker($this->getInvoker());
    $tpl->setTable($this->_table);
    $tpl->setUp();
    $tpl->setTableDefinition();
  }

  public function setUp()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.setup', array('object' => $this)));
  }

  public function isI18ned()
  {
    $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
    return isset($i18nedModels[$this->_table->getOption('name')]);
  }

  public function getI18nedFields()
  {
    if ($this->isI18ned())
    {
      $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
      return $i18nedModels[$this->_table->getOption('name')];
    } else {
      return array();
    }
  }

  public function isVersioned()
  {
    $versionedModels = sfSympalConfig::get('versioned_models', null, array());
    return isset($versionedModels[$this->_table->getOption('name')]);
  }

  public function isContent()
  {
    return $this->_table->getOption('name') == 'Content' ? true:false;
  }

  public function __call($method, $arguments)
  {
    $event = sfProjectConfiguration::getActive()->getEventDispatcher()->notifyUntil(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      return null;
    }

    return $event->getReturnValue();
  }
}

class sfSympalContentTemplate extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalContentFilter());
  }

  public function __call($method, $arguments)
  {
    try {
      return call_user_func_array(array($this->getInvoker()->getRecord(), $method), $arguments);
    } catch (Exception $e) {
      return null;
    }
  }
}

class sfSympalContentFilter extends Doctrine_Record_Filter
{
  public function init()
  {
    
  }

  public function filterSet(Doctrine_Record $record, $name, $value)
  {
      try {
          $record->getRecord()->$name = $value;
          return $record;
      } catch (Exception $e) {}

      throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }

  public function filterGet(Doctrine_Record $record, $name)
  {
      try {
          return $record->getRecord()->$name;
      } catch (Exception $e) {}

      throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
  }
}