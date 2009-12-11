<?php

class sfSympalI18nGenerator extends Doctrine_I18n
{
  protected $_invoker;

  public function setInvoker($invoker)
  {
    $this->_invoker = $invoker;
  }
}