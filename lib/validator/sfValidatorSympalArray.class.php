<?php

class sfValidatorSympalArray extends sfValidatorBase
{
  public function doClean($value)
  {
    return sfYaml::load($value);
  }
}