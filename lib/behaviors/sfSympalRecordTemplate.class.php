<?php

/**
 * Doctrine template for all models in Sympal to act as. Allows Sympal to add
 * things to all models easily.
 *
 * @package sfSympalPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalRecordTemplate extends Doctrine_Template
{
  /**
   * @var string $eventName The name of the event generated from the invoker class name
   */
  protected $_eventName;

  /**
   * @var string $modelName The fixed name of the model for this template instances invoker
   */
  protected $_modelName;

  /**
   * Override the setInvoker() method to allow us to generate the event name
   *
   * @param Doctrine_Record_Abstract $invoker 
   * @return void
   */
  public function setInvoker(Doctrine_Record_Abstract $invoker)
  {
    parent::setInvoker($invoker);
    $this->_eventName = sfInflector::tableize(get_class($invoker));
  }

  /**
   * Hook into the models setTableDefinition() process and handle some additional
   * setup with our models
   *
   * @return void
   */
  public function setTableDefinition()
  {
    // Add global Doctrine record filter which implements Symfony filter events for unknown property calls
    $this->_table->unshiftFilter(new sfSympalRecordEventFilter());

    // Attach sluggable behavior if is sluggable
    if ($this->isSluggable())
    {
      $this->sympalActAs('Doctrine_Template_Sluggable', $this->getSluggableOptions());
    }

    // Attach i18n behavior if is i18ned
    if ($this->isI18ned())
    {
      $this->sympalActAsI18n(array('fields' => $this->getI18nedFields()), 'Doctrine_Template_I18n');
    }

    // Attach content model template if this is the sfSympalContent model
    if ($this->isContent())
    {
      $this->sympalActAs('sfSympalContentModelTemplate');
    }

    // Invoke Symfony event to hook into every models setTableDefinition() process
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.set_table_definition', array('object' => $this)));
  }

  /**
   * Attach a Sympal template to this models
   *
   * @param mixed $tpl The template to act as
   * @param string $options The array of options for the template
   * @param string $name The name of the template
   * @return void
   */
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

  /**
   * Make this model act as i18n
   *
   * @param array $options Array of options for the i18n behavior
   * @param string $name The name of the i18n behavior
   * @return void
   */
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

  /**
   * Notify a Symfony event to allow us to hook into any models setUp() process
   *
   * @return void
   */
  public function setUp()
  {
    sfProjectConfiguration::getActive()->getEventDispatcher()->notify(new sfEvent($this->getInvoker(), 'sympal.'.$this->_eventName.'.setup', array('object' => $this)));
  }

  /**
   * Check if this model has i18n enabled
   *
   * @return boolean $result Whether or not i18n is enabled for this model
   */
  public function isI18ned()
  {
    $i18nedModels = sfSympalConfig::get('internationalized_models', null, array());
    return sfSympalConfig::get('i18n') && isset($i18nedModels[$this->getModelName()]);
  }

  /**
   * Get the array of fields that are i18ned for this model
   *
   * @return array $fields
   */
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

  /**
   * Check if this model has the sluggable behavior enabled
   *
   * @return boolean $result Whether or not this model is sluggable
   */
  public function isSluggable()
  {
    $sluggableModels = sfSympalConfig::get('sluggable_models', null, array());
    return array_key_exists($this->getModelName(), $sluggableModels);
  }

  /**
   * Get the array of options for the sluggable behavior
   *
   * @return array $options The array of options
   */
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

  /**
   * Check whether or not this template is attached to the sfSympalContent model
   *
   * @return boolean $result
   */
  public function isContent()
  {
    return $this->getModelName() == 'sfSympalContent' ? true : false;
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