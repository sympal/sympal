<?php

class Basesympal_registerComponents extends sfComponents
{
  public function executeForm()
  {
    $this->form = sfSympalRegisterForm::getInstance();
  }
}