<?php

/**
 * Doctrine template for the sfSympalContent Doctrine model to implement.
 * Attaches the sfSympalContentFilter template to forward property calls
 * to the content type record instance. Also implements a __call() method to forward
 * methods calls to the content type record as well.
 *
 * @see sfSympalContentFilter
 * @package sfSympalCMFPlugin
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class sfSympalContentModelTemplate extends Doctrine_Template
{
  /**
   * Hook into the sfSympalContent setTableDefinition() process and attach the
   * sfSympalContentFilter instance
   *
   * @return void
   */
  public function setTableDefinition()
  {
    $this->_table->unshiftFilter(new sfSympalContentFilter());
  }

  /**
   * Forward method unknown method calls to the content type instance
   *
   * @param string $method The method name called
   * @param array $arguments The array of arguments passed to the method
   * @return mixed $return
   */
  public function __call($method, $arguments)
  {
    try {
      return call_user_func_array(array($this->getInvoker()->getRecord(), $method), $arguments);
    } catch (Exception $e) {
      return null;
    }
  }
}