<?php

class sfSympalContentModelTemplate extends Doctrine_Template
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