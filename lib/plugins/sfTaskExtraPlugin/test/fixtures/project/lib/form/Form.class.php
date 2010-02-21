<?php

class Form extends BaseForm
{
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = false)
  {
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  public function configure()
  {
  }
}
