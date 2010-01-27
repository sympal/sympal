<?php

class sfSympalRecordTemplate extends Doctrine_Template
{
  protected
    $_eventName,
    $_modelName;

  public function setInvoker(Doctrine_Record_Abstract $invoker)
  {
    parent::setInvoker($invoker);
    $this->_eventName = sfInflector::tableize(get_class($invoker));
  }

  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalRecordEventFilter());

    if ($this->isSluggable())
    {
      $this->sympalActAs('Doctrine_Template_Sluggable', $this->getSluggableOptions());
    }

    if ($this->isI18ned())
    {
      $this->sympalActAsI18n(array('fields' => $this->getI18nedFields()), 'Doctrine_Template_I18n');
    }

    if ($this->isContent())
    {
      $this->sympalActAs('sfSympalContentModelTemplate');
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

  public function sympalActAsI18n($options = array(), $name = null)
  {
    $this->sympalActAs('Doctrine_Template_I18n', $options, $name);

    if (!$this->_table->getOption('has_symfony_i18n_filter'))
    {
      $this->_table
        ->unshiftFilter(new sfSympalDoctrineRecordI18nFilter())
        ->setOption('has_symfony_i18n_filter', true)
      ;
    }
  }

  public function setUp()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.setup', array('object' => $this)));
  }

  public function isI18ned()
  {
    $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
    return sfSympalConfig::get('i18n') && isset($i18nedModels[$this->getModelName()]);
  }

  public function getI18nedFields()
  {
    if ($this->isI18ned())
    {
      $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
      return $i18nedModels[$this->getModelName()];
    } else {
      return array();
    }
  }

  public function isSluggable()
  {
    $sluggableModels = sfSympalConfig::get('sluggable_models', null, array());
    return array_key_exists($this->getModelName(), $sluggableModels);
  }

  public function getSluggableOptions()
  {
    if ($this->isSluggable())
    {
      $sluggableModels = sfSympalConfig::get('sluggable_models', null, array());
      return $sluggableModels[$this->getModelName()] ? $sluggableModels[$this->getModelName()]:array();
    } else {
      return array();
    }
  }

  public function isContent()
  {
    return $this->getModelName() == 'sfSympalContent' ? true:false;
  }

  /**
   * Hack for working around the ToPrfx and FromPrfx prefix used by migrations
   *
   * @return string $modelName
   */
  public function getModelName()
  {
    if (!$this->_modelName)
    {
      $this->_modelName = str_replace('ToPrfx', '', $this->_table->getOption('name'));
      $this->_modelName = str_replace('FromPrfx' ,'', $this->_modelName);
    }
    return $this->_modelName;
  }
}