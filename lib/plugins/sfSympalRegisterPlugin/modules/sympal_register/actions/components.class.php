<?php

class sympal_registerComponents extends sfComponents
{
  public function executeForm()
  {
    $this->form = sfSympalRegisterForm::getInstance();
  }
}