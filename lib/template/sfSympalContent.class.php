<?php

class sfSympalContent extends Doctrine_Template
{
  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalSharedPropertiesFilter($this->_table));
  }

  public function __call($method, $arguments)
  {
    $invoker = $this->getInvoker();

    $contentTypes = sfSympalToolkit::getContentTypesCache();
    foreach ($contentTypes as $contentType)
    {
      try {
        return call_user_func_array(array($invoker->$contentType, $method), $arguments);
      } catch (Exception $e) {
        continue;
      }
    }
  }
}