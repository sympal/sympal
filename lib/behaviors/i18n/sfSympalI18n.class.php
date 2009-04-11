<?php

class sfSympalI18n extends Doctrine_Template_I18n
{
  public function __construct(array $options = array())
  {
    $this->_plugin = new sfSympalI18nGenerator($options);
  }

  public function setUp()
  {
    $this->_plugin->setInvoker($this->getInvoker());
    $this->_plugin->initialize($this->_table);
  }
}